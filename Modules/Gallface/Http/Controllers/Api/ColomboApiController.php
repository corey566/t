
<?php

namespace Modules\Gallface\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ColomboApiController extends Controller
{
    /**
     * Receive sales data from Colombo City Center
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function receiveSalesData(Request $request)
    {
        $startTime = microtime(true);
        
        // Log incoming request
        $this->logApiRequest('POST', '/api/colombo-city/sales-data', $request->all(), null);
        
        try {
            $validator = Validator::make($request->all(), [
                'Transactions' => 'required|array',
                'Transactions.*.LOCATION_CODE' => 'required',
                'Transactions.*.TERMINAL_ID' => 'required',
                'Transactions.*.RCPT_NUM' => 'required',
                'Transactions.*.RCPT_DT' => 'required',
                'Transactions.*.BUSINESS_DT' => 'required',
                'Transactions.*.INV_AMT' => 'required|numeric',
                'Transactions.*.TRAN_STATUS' => 'required|in:SALES,RETURN',
            ]);

            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ];
                
                $this->logApiRequest('POST', '/api/colombo-city/sales-data', $request->all(), $response, 'failed', microtime(true) - $startTime);
                
                return response()->json($response, 422);
            }

            $transactions = $request->input('Transactions', []);
            $processedCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($transactions as $transaction) {
                try {
                    $this->processTransaction($transaction);
                    $processedCount++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'receipt_num' => $transaction['RCPT_NUM'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ];
                    Log::error('Colombo City API: Transaction processing failed', [
                        'transaction' => $transaction,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            $response = [
                'success' => true,
                'message' => "Processed {$processedCount} transactions",
                'processed_count' => $processedCount,
                'errors' => $errors
            ];
            
            $this->logApiRequest('POST', '/api/colombo-city/sales-data', $request->all(), $response, 'success', microtime(true) - $startTime);

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Colombo City API: Batch processing failed', [
                'error' => $e->getMessage()
            ]);

            $response = [
                'success' => false,
                'message' => 'Failed to process transactions: ' . $e->getMessage()
            ];
            
            $this->logApiRequest('POST', '/api/colombo-city/sales-data', $request->all(), $response, 'failed', microtime(true) - $startTime);

            return response()->json($response, 500);
        }
    }

    /**
     * Process individual transaction
     */
    private function processTransaction($transaction)
    {
        // Store transaction in database
        DB::table('colombo_city_transactions')->insert([
            'location_code' => $transaction['LOCATION_CODE'],
            'terminal_id' => $transaction['TERMINAL_ID'],
            'shift_no' => $transaction['SHIFT_NO'] ?? '1',
            'receipt_num' => $transaction['RCPT_NUM'],
            'receipt_date' => $this->parseDate($transaction['RCPT_DT']),
            'business_date' => $this->parseDate($transaction['BUSINESS_DT']),
            'receipt_time' => $this->parseTime($transaction['RCPT_TM'] ?? '000000'),
            'invoice_amount' => $transaction['INV_AMT'],
            'tax_amount' => $transaction['TAX_AMT'] ?? 0,
            'return_amount' => $transaction['RET_AMT'] ?? 0,
            'transaction_status' => $transaction['TRAN_STATUS'],
            'operational_currency' => $transaction['OP_CUR'] ?? 'LKR',
            'exchange_rate' => $transaction['BC_EXCH'] ?? 1,
            'discount' => $transaction['DISCOUNT'] ?? 0,
            'raw_data' => json_encode($transaction),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Process item details
        if (isset($transaction['ItemDetail']) && is_array($transaction['ItemDetail'])) {
            foreach ($transaction['ItemDetail'] as $item) {
                $this->processItem($transaction['RCPT_NUM'], $item);
            }
        }

        // Process payment details
        if (isset($transaction['PaymentDetail']) && is_array($transaction['PaymentDetail'])) {
            foreach ($transaction['PaymentDetail'] as $payment) {
                $this->processPayment($transaction['RCPT_NUM'], $payment);
            }
        }
    }

    /**
     * Process item details
     */
    private function processItem($receiptNum, $item)
    {
        DB::table('colombo_city_items')->insert([
            'receipt_num' => $receiptNum,
            'item_code' => $item['ITEM_CODE'],
            'item_name' => $item['ITEM_NAME'],
            'item_qty' => $item['ITEM_QTY'],
            'item_price' => $item['ITEM_PRICE'],
            'item_category' => $item['ITEM_CAT'] ?? '',
            'item_tax' => $item['ITEM_TAX'] ?? 0,
            'item_tax_type' => $item['ITEM_TAX_TYPE'] ?? 'I',
            'item_net_amount' => $item['ITEM_NET_AMT'],
            'operational_currency' => $item['OP_CUR'] ?? 'LKR',
            'exchange_rate' => $item['BC_EXCH'] ?? 1,
            'item_status' => $item['ITEM_STATUS'],
            'item_discount' => $item['ITEM_DISCOUNT'] ?? 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Process payment details
     */
    private function processPayment($receiptNum, $payment)
    {
        DB::table('colombo_city_payments')->insert([
            'receipt_num' => $receiptNum,
            'payment_name' => $payment['PAYMENT_NAME'],
            'currency_code' => $payment['CURRENCY_CODE'],
            'exchange_rate' => $payment['EXCHANGE_RATE'] ?? 1,
            'tender_amount' => $payment['TENDER_AMOUNT'],
            'operational_currency' => $payment['OP_CUR'] ?? 'LKR',
            'bc_exchange_rate' => $payment['BC_EXCH'] ?? 1,
            'payment_status' => $payment['PAYMENT_STATUS'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Parse date from YYYYMMDD format
     */
    private function parseDate($dateStr)
    {
        return Carbon::createFromFormat('Ymd', $dateStr)->format('Y-m-d');
    }

    /**
     * Parse time from HHMMSS format
     */
    private function parseTime($timeStr)
    {
        return Carbon::createFromFormat('His', $timeStr)->format('H:i:s');
    }

    /**
     * Get transactions by date range
     */
    public function getTransactions(Request $request)
    {
        $startTime = microtime(true);
        $this->logApiRequest('GET', '/api/colombo-city/transactions', $request->all(), null);
        
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date_format:Y-m-d',
            'to_date' => 'required|date_format:Y-m-d',
            'location_code' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'errors' => $validator->errors()
            ];
            
            $this->logApiRequest('GET', '/api/colombo-city/transactions', $request->all(), $response, 'failed', microtime(true) - $startTime);
            
            return response()->json($response, 422);
        }

        $query = DB::table('colombo_city_transactions')
            ->whereBetween('business_date', [$request->from_date, $request->to_date]);

        if ($request->has('location_code')) {
            $query->where('location_code', $request->location_code);
        }

        $transactions = $query->get();

        $response = [
            'success' => true,
            'data' => $transactions
        ];
        
        $this->logApiRequest('GET', '/api/colombo-city/transactions', $request->all(), $response, 'success', microtime(true) - $startTime);

        return response()->json($response);
    }

    /**
     * Test API connection
     */
    public function testConnection(Request $request)
    {
        $startTime = microtime(true);
        $this->logApiRequest('GET', '/api/colombo-city/test-connection', [], null);
        
        $response = [
            'success' => true,
            'message' => 'Colombo City Center API is operational',
            'timestamp' => now()->toIso8601String()
        ];
        
        $this->logApiRequest('GET', '/api/colombo-city/test-connection', [], $response, 'success', microtime(true) - $startTime);
        
        return response()->json($response);
    }

    /**
     * Log API request to sync logs
     */
    private function logApiRequest($method, $endpoint, $requestData, $responseData, $status = 'pending', $duration = 0)
    {
        try {
            DB::table('colombo_sync_logs')->insert([
                'request_method' => $method,
                'endpoint' => $endpoint,
                'request_data' => json_encode($requestData),
                'response_data' => json_encode($responseData),
                'status' => $status,
                'duration_ms' => round($duration * 1000, 2),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log API request', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
