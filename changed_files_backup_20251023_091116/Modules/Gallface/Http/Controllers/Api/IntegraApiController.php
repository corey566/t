
<?php

namespace Modules\Gallface\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class IntegraApiController extends Controller
{
    /**
     * Receive sales invoice data from Integra POS system
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function receiveSalesInvoice(Request $request)
    {
        $startTime = microtime(true);
        $ipAddress = $request->ip();
        
        // Check authentication
        $authResult = $this->authenticateRequest($request);
        if (!$authResult['success']) {
            $this->logApiRequest($request, 'authentication_failed', $authResult['message']);
            return response()->json([
                'status' => 'error',
                'message' => $authResult['message']
            ], 401);
        }

        // Parse request data (handle both JSON and XML)
        $data = $this->parseRequestData($request);
        
        if (!$data) {
            $this->logApiRequest($request, 'parse_error', 'Invalid request format');
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid request format. Accepted formats: JSON, XML'
            ], 400);
        }

        // Validate required fields
        $validation = $this->validateIntegraData($data);
        if (!$validation['success']) {
            $this->logApiRequest($request, 'validation_failed', $validation['message'], $data);
            return response()->json([
                'status' => 'error',
                'message' => $validation['message']
            ], 422);
        }

        // Process and store the invoice data
        try {
            $this->processInvoiceData($data);
            
            $receivedAt = Carbon::now()->toIso8601String();
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->logApiRequest($request, 'success', 'Sales invoice received successfully', $data, $duration);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Sales invoice received successfully',
                'received_at' => $receivedAt
            ], 200);

        } catch (\Exception $e) {
            Log::error('Integra API: Failed to process invoice', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->logApiRequest($request, 'processing_error', $e->getMessage(), $data);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process sales invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Authenticate the request using Basic Authentication
     */
    private function authenticateRequest(Request $request)
    {
        $username = env('INTEGRA_API_USER');
        $password = env('INTEGRA_API_PASS');

        if (!$username || !$password) {
            return [
                'success' => false,
                'message' => 'API credentials not configured'
            ];
        }

        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Basic ')) {
            return [
                'success' => false,
                'message' => 'Missing or invalid Authorization header'
            ];
        }

        $credentials = base64_decode(substr($authHeader, 6));
        list($providedUsername, $providedPassword) = explode(':', $credentials, 2);

        if ($providedUsername !== $username || $providedPassword !== $password) {
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }

        return ['success' => true];
    }

    /**
     * Parse request data from JSON or XML
     */
    private function parseRequestData(Request $request)
    {
        $contentType = $request->header('Content-Type');

        // Try JSON first
        if (str_contains($contentType, 'application/json') || $request->isJson()) {
            return $request->all();
        }

        // Try XML
        if (str_contains($contentType, 'application/xml') || str_contains($contentType, 'text/xml')) {
            try {
                $xml = simplexml_load_string($request->getContent());
                if ($xml === false) {
                    return null;
                }
                return json_decode(json_encode($xml), true);
            } catch (\Exception $e) {
                Log::error('Integra API: XML parsing failed', ['error' => $e->getMessage()]);
                return null;
            }
        }

        // Default: try to parse as JSON
        return $request->all();
    }

    /**
     * Validate Integra data format
     */
    private function validateIntegraData($data)
    {
        // Check for TransactionDetails
        if (!isset($data['TransactionDetails'])) {
            return [
                'success' => false,
                'message' => 'Missing required section: TransactionDetails'
            ];
        }

        $transaction = $data['TransactionDetails'];

        // Validate required transaction fields
        $requiredFields = ['LOCATION_CODE', 'RCPT_NUM', 'TRAN_STATUS'];
        
        foreach ($requiredFields as $field) {
            if (!isset($transaction[$field]) || empty($transaction[$field])) {
                return [
                    'success' => false,
                    'message' => "Missing required field: {$field}"
                ];
            }
        }

        // Validate TRAN_STATUS values
        $validStatuses = ['SALES', 'RETURN', 'VOID'];
        if (!in_array(strtoupper($transaction['TRAN_STATUS']), $validStatuses)) {
            return [
                'success' => false,
                'message' => 'Invalid TRAN_STATUS. Allowed values: SALES, RETURN, VOID'
            ];
        }

        return ['success' => true];
    }

    /**
     * Process and store invoice data
     */
    private function processInvoiceData($data)
    {
        DB::beginTransaction();

        try {
            $transaction = $data['TransactionDetails'];
            
            // Store transaction details
            $transactionId = DB::table('integra_transactions')->insertGetId([
                'location_code' => $transaction['LOCATION_CODE'],
                'terminal_id' => $transaction['TERMINAL_ID'] ?? null,
                'receipt_num' => $transaction['RCPT_NUM'],
                'receipt_date' => isset($transaction['RCPT_DT']) ? Carbon::parse($transaction['RCPT_DT']) : now(),
                'business_date' => isset($transaction['BUSINESS_DT']) ? Carbon::parse($transaction['BUSINESS_DT']) : now(),
                'transaction_status' => strtoupper($transaction['TRAN_STATUS']),
                'invoice_amount' => $transaction['INV_AMT'] ?? 0,
                'tax_amount' => $transaction['TAX_AMT'] ?? 0,
                'discount_amount' => $transaction['DISCOUNT'] ?? 0,
                'operational_currency' => $transaction['OP_CUR'] ?? 'LKR',
                'exchange_rate' => $transaction['BC_EXCH'] ?? 1,
                'raw_data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Store item details if present
            if (isset($data['ItemDetails']) && is_array($data['ItemDetails'])) {
                foreach ($data['ItemDetails'] as $item) {
                    DB::table('integra_items')->insert([
                        'transaction_id' => $transactionId,
                        'item_code' => $item['ITEM_CODE'] ?? null,
                        'item_name' => $item['ITEM_NAME'] ?? null,
                        'item_qty' => $item['ITEM_QTY'] ?? 0,
                        'item_price' => $item['ITEM_PRICE'] ?? 0,
                        'item_category' => $item['ITEM_CAT'] ?? null,
                        'item_tax' => $item['ITEM_TAX'] ?? 0,
                        'item_tax_type' => $item['ITEM_TAX_TYPE'] ?? 'I',
                        'item_net_amount' => $item['ITEM_NET_AMT'] ?? 0,
                        'item_status' => $item['ITEM_STATUS'] ?? null,
                        'item_discount' => $item['ITEM_DISCOUNT'] ?? 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // Store payment details if present
            if (isset($data['PaymentDetails']) && is_array($data['PaymentDetails'])) {
                foreach ($data['PaymentDetails'] as $payment) {
                    DB::table('integra_payments')->insert([
                        'transaction_id' => $transactionId,
                        'payment_name' => $payment['PAYMENT_NAME'] ?? null,
                        'currency_code' => $payment['CURRENCY_CODE'] ?? 'LKR',
                        'exchange_rate' => $payment['EXCHANGE_RATE'] ?? 1,
                        'tender_amount' => $payment['TENDER_AMOUNT'] ?? 0,
                        'payment_status' => $payment['PAYMENT_STATUS'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Log API request to database
     */
    private function logApiRequest(Request $request, $status, $message, $data = null, $duration = 0)
    {
        try {
            DB::table('integra_api_logs')->insert([
                'ip_address' => $request->ip(),
                'request_method' => $request->method(),
                'request_uri' => $request->getRequestUri(),
                'request_headers' => json_encode($request->headers->all()),
                'request_body' => $data ? json_encode($data) : $request->getContent(),
                'status' => $status,
                'message' => $message,
                'duration_ms' => $duration,
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Integra API: Failed to log request', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle unsupported HTTP methods
     */
    public function handleUnsupportedMethod()
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Method not allowed. Only POST requests are accepted.'
        ], 405);
    }
}
