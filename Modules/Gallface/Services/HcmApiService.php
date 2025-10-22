<?php

namespace Modules\Gallface\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class HcmApiService
{
    protected $apiUrl;
    protected $tenantId;
    protected $tenantSecret;
    protected $stallNo;
    protected $posId;
    protected $token;

    protected $pingInterval;

    public function __construct($credentials)
    {
        $this->apiUrl = $credentials['api_url'] ?? 'https://trms-api.azurewebsites.net';
        $this->tenantId = $credentials['username'] ?? ''; // Using username field for tenant_id
        $this->tenantSecret = $credentials['password'] ?? ''; // Using password field for tenant_secret
        $this->stallNo = $credentials['stall_no'] ?? '';
        $this->posId = $credentials['pos_id'] ?? '';
        $this->pingInterval = $credentials['ping_interval'] ?? 5; // Default 5 minutes
    }

    /**
     * Get authentication token from HCM API
     * Uses /api/token endpoint with tenant_id and tenant_secret
     */
    protected function getAuthToken($forceRefresh = false)
    {
        // Check cache for existing valid token
        $cacheKey = 'hcm_token_' . $this->tenantId;

        if (!$forceRefresh) {
            $cachedToken = Cache::get($cacheKey);
            if ($cachedToken) {
                Log::info('Using cached HCM token', [
                    'tenant_id' => $this->tenantId,
                    'token_preview' => substr($cachedToken, 0, 20) . '...'
                ]);
                return $cachedToken;
            }
        }

        try {
            // Prepare authentication request
            $authData = [
                'tenant_id' => $this->tenantId,
                'tenant_secret' => $this->tenantSecret,
                'grant_type' => 'client_credentials'
            ];

            Log::info('HCM Token Request', [
                'url' => $this->apiUrl . '/api/token',
                'tenant_id' => $this->tenantId,
                'tenant_secret_provided' => !empty($this->tenantSecret),
                'api_url' => $this->apiUrl,
                'request_data' => $authData
            ]);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json'
                ])
                ->post($this->apiUrl . '/api/token', $authData);

            Log::info('HCM Token Response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'] ?? null;
                $expiresIn = $data['expires_in'] ?? 480; // Default 480 seconds (8 minutes)

                if ($token) {
                    // Cache token for slightly less than expiry time to be safe
                    Cache::put($cacheKey, $token, now()->addSeconds($expiresIn - 30));

                    Log::info('HCM Token Generated and Cached', [
                        'tenant_id' => $this->tenantId,
                        'expires_in' => $expiresIn,
                        'token_preview' => substr($token, 0, 20) . '...'
                    ]);

                    return $token;
                }
            }

            Log::error('HCM Token Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('HCM Token Exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Test API connection
     */
    public function testConnection()
    {
        try {
            $token = $this->getAuthToken();

            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'Failed to obtain authentication token'
                ];
            }

            return [
                'success' => true,
                'message' => 'HCM API connection successful - Token obtained'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sync sales data to HCM API
     */
    public function syncSales($salesData, $locationId)
    {
        try {
            $token = $this->getAuthToken();

            if (!$token) {
                Log::error('HCM Token Failed', [
                    'tenant_id' => $this->tenantId,
                    'api_url' => $this->apiUrl
                ]);
                return [
                    'success' => false,
                    'message' => 'Failed to obtain authentication token',
                    'records_synced' => 0
                ];
            }

            Log::info('HCM Token Obtained Successfully', [
                'token_preview' => substr($token, 0, 20) . '...'
            ]);

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($salesData as $sale) {
                try {
                    // Convert to object if it's an array
                    $saleObj = is_array($sale) ? (object)$sale : $sale;

                    $formattedInvoice = $this->formatInvoiceForHcm($saleObj);

                    // Submit invoice directly to /api/invoices with Bearer token
                    Log::info('HCM Invoice Submission Request', [
                        'url' => $this->apiUrl . '/api/invoices',
                        'data' => $formattedInvoice
                    ]);

                    $response = Http::timeout(30)
                        ->withHeaders([
                            'Authorization' => 'Bearer ' . $token,
                            'Content-Type' => 'application/json'
                        ])
                        ->post($this->apiUrl . '/api/invoices', $formattedInvoice);

                    $statusCode = $response->status();
                    $responseBody = $response->body();
                    $responseJson = $response->json();

                    Log::info('HCM Invoice Submission Response', [
                        'status' => $statusCode,
                        'body' => $responseJson
                    ]);

                    // If request fails with 401, refresh token and retry
                    if ($statusCode === 401) {
                        Log::warning('Token expired or invalid, refreshing and retrying', [
                            'invoice_no' => $saleObj->invoice_no ?? 'Unknown'
                        ]);

                        $token = $this->getAuthToken(true); // Force refresh

                        if (!$token) {
                            throw new \Exception('Failed to refresh authentication token');
                        }

                        $response = Http::timeout(30)
                            ->withHeaders([
                                'Authorization' => 'Bearer ' . $token,
                                'Content-Type' => 'application/json'
                            ])
                            ->post($this->apiUrl . '/api/invoices', $formattedInvoice);

                        $statusCode = $response->status();
                        $responseBody = $response->body();
                        $responseJson = $response->json();

                        Log::info('HCM Invoice Submission Response (After retry)', [
                            'status' => $statusCode,
                            'body' => $responseJson
                        ]);
                    }

                    if ($response->successful()) {
                        $successCount++;
                        Log::info('✓ Invoice Synced Successfully', [
                            'invoice_no' => $saleObj->invoice_no ?? 'Unknown',
                            'response' => $responseJson
                        ]);
                    } else {
                        $failedCount++;

                        $errorMessage = $responseBody;
                        if (is_array($responseJson)) {
                            $errorMessage = json_encode($responseJson);
                        } elseif (isset($responseJson['message'])) {
                            $errorMessage = $responseJson['message'];
                        } elseif (isset($responseJson['error'])) {
                            $errorMessage = $responseJson['error'];
                        }

                        Log::error('✗ Invoice Submission Failed', [
                            'invoice_no' => $saleObj->invoice_no ?? 'Unknown',
                            'status_code' => $statusCode,
                            'error_message' => $errorMessage,
                            'sent_data' => $formattedInvoice
                        ]);

                        $errors[] = [
                            'invoice_no' => $saleObj->invoice_no ?? 'Unknown',
                            'status_code' => $statusCode,
                            'error_message' => $errorMessage,
                            'error_details' => $responseJson
                        ];
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    Log::error('HCM Invoice Sync Exception', [
                        'invoice_no' => isset($sale->invoice_no) ? $sale->invoice_no : (is_array($sale) && isset($sale['invoice_no']) ? $sale['invoice_no'] : 'Unknown'),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    $errors[] = [
                        'invoice_no' => isset($sale->invoice_no) ? $sale->invoice_no : (is_array($sale) && isset($sale['invoice_no']) ? $sale['invoice_no'] : 'Unknown'),
                        'error_message' => 'Exception: ' . $e->getMessage(),
                        'exception_trace' => $e->getTraceAsString()
                    ];
                }
            }

            $summary = [
                'success' => $successCount > 0,
                'message' => "Synced {$successCount} invoices successfully" . ($failedCount > 0 ? ", {$failedCount} failed" : ''),
                'records_synced' => $successCount,
                'records_failed' => $failedCount,
                'errors' => $errors
            ];

            Log::info('HCM Sync Summary', $summary);

            return $summary;

        } catch (\Exception $e) {
            Log::error('HCM Sales Sync Error', [
                'location_id' => $locationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Sync error: ' . $e->getMessage(),
                'records_synced' => 0
            ];
        }
    }

    /**
     * Format invoice data according to HCM API documentation
     * CRITICAL: Field order and exact format must match API specification
     */
    protected function formatInvoiceForHcm($sale)
    {
        // Log raw sale data for debugging
        Log::info('Formatting invoice for HCM', [
            'raw_invoice_no' => $sale->invoice_no ?? 'NULL',
            'raw_type' => $sale->type ?? 'NULL',
            'raw_transaction_date' => $sale->transaction_date ?? 'NULL',
            'tenant_id' => $this->tenantId,
            'pos_id' => $this->posId,
            'stall_no' => $this->stallNo
        ]);

        // Determine invoice type - STRICT: Must be one of: Sale, Return, Void, Refund, Exchange (max 12 chars)
        $invoiceType = 'Sale';
        if (isset($sale->type)) {
            $typeMap = [
                'sell_return' => 'Return',
                'sell' => 'Sale',
                'void' => 'Void',
                'refund' => 'Refund',
                'exchange' => 'Exchange',
            ];
            $invoiceType = $typeMap[$sale->type] ?? 'Sale';
        }

        // Validate critical fields according to API spec
        $tenantId = trim(substr((string)$this->tenantId, 0, 15)); // Max 15 chars
        $posId = trim(substr((string)$this->posId, 0, 25)); // Max 25 chars
        $stallNo = trim(substr((string)$this->stallNo, 0, 10)); // Max 10 chars
        $invoiceNo = trim((string)($sale->invoice_no ?? '')); // REQUIRED

        // Validate required fields
        if (empty($tenantId)) {
            throw new \Exception("Tenant ID is required and cannot be blank");
        }
        if (empty($posId)) {
            throw new \Exception("POS ID is required and cannot be blank");
        }
        if (empty($stallNo)) {
            throw new \Exception("Stall No is required and cannot be blank");
        }
        if (empty($invoiceNo)) {
            throw new \Exception("Invoice No is required and cannot be blank");
        }

        $cashierId = substr((string)($sale->cashier_id ?? $sale->created_by ?? 'CASH-01'), 0, 50);

        // Set customer mobile to blank if not available (don't send random numbers)
        $customerMobile = '';
        if (!empty($sale->mobile)) {
            $customerMobile = substr((string)$sale->mobile, 0, 15);
        } elseif (!empty($sale->customer_mobile)) {
            $customerMobile = substr((string)$sale->customer_mobile, 0, 15);
        }

        // Get payment details
        $paidByCash = 0.00;
        $paidByCard = 0.00;
        $cardBank = '';
        $cardCategory = '';
        $cardType = '';
        $hcmLoyalty = 0.00;
        $tenantLoyalty = 0.00;
        $creditNotes = 0.00;
        $otherPayments = 0.00;
        $havelockCityVoucher = 0.00;

        if (isset($sale->payment_lines) && (is_array($sale->payment_lines) || is_object($sale->payment_lines))) {
            foreach ($sale->payment_lines as $payment) {
                $paymentObj = is_array($payment) ? (object)$payment : $payment;
                $amount = floatval($paymentObj->amount ?? 0);
                $method = strtolower($paymentObj->method ?? '');

                if ($method === 'cash') {
                    $paidByCash += $amount;
                } elseif (in_array($method, ['card', 'credit_card', 'debit_card'])) {
                    $paidByCard += $amount;
                    $cardBank = $paymentObj->card_bank ?? '';
                    $cardCategory = $paymentObj->card_category ?? '';
                    $cardType = $paymentObj->card_type ?? '';
                } elseif ($method === 'hcm_loyalty') {
                    $hcmLoyalty += $amount;
                } elseif ($method === 'tenant_loyalty' || $method === 'loyalty') {
                    $tenantLoyalty += $amount;
                } elseif ($method === 'credit_note') {
                    $creditNotes += $amount;
                } elseif ($method === 'havelock_city_voucher' || $method === 'havelock city voucher') {
                    $havelockCityVoucher += $amount;
                } else {
                    $otherPayments += $amount;
                }
            }
        }

        // Use reward points redeemed amount for gift voucher burn
        // rp_redeemed_amount represents the monetary value of reward points used
        if (!isset($giftVoucherBurn)) {
            $giftVoucherBurn = floatval($sale->rp_redeemed_amount ?? 0);
        }

        // Map reward points to gift voucher and loyalty amounts
        // rp_redeemed_amount is used for gift voucher burn
        $giftVoucherBurn = floatval($sale->rp_redeemed_amount ?? 0);

        // Calculate gift voucher totals (if this is a gift voucher sale)
        $totalGiftVoucherSale = 0.00;
        $totalGiftVoucherTax = 0.00;
        $totalGiftVoucherDiscount = 0.00;

        // Check if this transaction has gift voucher sales based on additional data or transaction type
        if (isset($sale->is_gift_voucher) && $sale->is_gift_voucher) {
            $totalGiftVoucherSale = floatval($sale->final_total ?? 0);
            $totalGiftVoucherTax = floatval($sale->tax_amount ?? 0);
            $totalGiftVoucherDiscount = floatval($sale->discount_amount ?? 0);
        }

        // Format date exactly as API expects: dd/MM/yyyy HH:mm:ss
        $invoiceDate = isset($sale->transaction_date) ? 
            Carbon::parse($sale->transaction_date)->format('d/m/Y H:i:s') : 
            Carbon::now()->format('d/m/Y H:i:s');

        // Return array in exact order as per API specification
        return [
            'tenantId' => $tenantId,
            'posId' => $posId,
            'stallNo' => $stallNo,
            'cashierId' => $cashierId,
            'customerMobileNo' => $customerMobile,
            'invoiceType' => $invoiceType,
            'invoiceNo' => $invoiceNo,
            'invoiceDate' => $invoiceDate,
            'currencyCode' => 'LKR',
            'currencyRate' => 1.0000,
            'totalInvoice' => round(floatval($sale->final_total ?? 0), 2),
            'totalTax' => round(floatval($sale->tax_amount ?? 0) - $totalGiftVoucherTax, 2),
            'totalDiscount' => round(floatval($sale->discount_amount ?? 0) - $totalGiftVoucherDiscount, 2),
            'totalGiftVoucherSale' => round($totalGiftVoucherSale, 2),
            'totalGiftVoucherTax' => round($totalGiftVoucherTax, 2),
            'totalGiftVoucherDiscount' => round($totalGiftVoucherDiscount, 2),
            'paidByCash' => round($paidByCash, 2),
            'paidByCard' => round($paidByCard, 2),
            'cardBank' => $cardBank,
            'cardCategory' => $cardCategory,
            'cardType' => $cardType,
            'GiftVoucherBurn' => round($giftVoucherBurn, 2),
            'hcmLoyalty' => round($hcmLoyalty, 2),
            'tenantLoyalty' => round($tenantLoyalty, 2),
            'creditNotes' => round($creditNotes, 2),
            'havelockCityVoucher' => round($havelockCityVoucher, 2),
            'otherPayments' => round($otherPayments, 2)
        ];
    }

    /**
     * Send ping to HCM API
     */
    public function sendPing($userId = null, $username = null, $ipAddress = null)
    {
        try {
            $token = $this->getAuthToken();

            if (!$token) {
                Log::error('HCM Ping Failed - No Token', [
                    'tenant_id' => $this->tenantId,
                    'pos_id' => $this->posId,
                    'user_id' => $userId,
                    'username' => $username,
                    'ip_address' => $ipAddress
                ]);
                return [
                    'success' => false,
                    'message' => 'Failed to obtain authentication token'
                ];
            }

            // Send ping request to HCM API - as per documentation
            $pingData = [
                'tenantId' => $this->tenantId,
                'posId' => $this->posId,
                'stallNo' => $this->stallNo,
                'timestamp' => Carbon::now()->format('d/m/Y H:i:s')
            ];

            Log::info('HCM Ping Request', [
                'url' => $this->apiUrl . '/api/ping',
                'data' => $pingData,
                'user_id' => $userId,
                'username' => $username,
                'ip_address' => $ipAddress
            ]);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ])
                ->post($this->apiUrl . '/api/ping', $pingData);

            $statusCode = $response->status();
            $responseData = $response->json();

            if ($statusCode === 200 || $response->successful()) {
                Log::info('HCM Ping Successful', [
                    'tenant_id' => $this->tenantId,
                    'pos_id' => $this->posId,
                    'user_id' => $userId,
                    'username' => $username,
                    'ip_address' => $ipAddress,
                    'response' => $responseData
                ]);

                return [
                    'success' => true,
                    'message' => 'Ping successful - POS online',
                    'response' => $responseData,
                    'user_info' => [
                        'user_id' => $userId,
                        'username' => $username,
                        'ip_address' => $ipAddress
                    ]
                ];
            }

            Log::warning('HCM Ping Failed', [
                'status' => $statusCode,
                'body' => $response->body(),
                'json' => $responseData,
                'user_id' => $userId,
                'username' => $username,
                'ip_address' => $ipAddress
            ]);

            return [
                'success' => false,
                'message' => 'Ping failed: ' . ($responseData['message'] ?? $response->body())
            ];
        } catch (\Exception $e) {
            Log::error('HCM Ping Exception', [
                'error' => $e->getMessage(),
                'tenant_id' => $this->tenantId,
                'pos_id' => $this->posId,
                'user_id' => $userId,
                'username' => $username,
                'ip_address' => $ipAddress,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Ping error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload Excel data for day/month end
     */
    public function uploadExcelData($excelData)
    {
        try {
            $token = $this->getAuthToken();

            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'Failed to obtain authentication token'
                ];
            }

            // Process Excel data and send to API
            // Note: This would need to be implemented based on specific Excel format

            return [
                'success' => true,
                'message' => 'Excel data upload functionality ready - implement based on specific format'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Upload error: ' . $e->getMessage()
            ];
        }
    }
}