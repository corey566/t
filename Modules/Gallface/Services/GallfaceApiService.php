<?php

namespace Modules\Gallface\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class GallfaceApiService
{
    private $tokenUrl;
    private $productionUrl;
    private $clientId;
    private $clientSecret;
    private $propertyCode;
    private $posInterfaceCode;
    private $appCode;

    public function __construct(array $credentials)
    {
        $this->tokenUrl = $credentials['access_token_url'] ?? 'https://mims.imonitor.center/connect/token';
        $this->productionUrl = $credentials['production_url'] ?? 'https://mims.imonitor.center';
        $this->clientId = $credentials['client_id'] ?? '';
        $this->clientSecret = $credentials['client_secret'] ?? '';
        $this->propertyCode = $credentials['property_code'] ?? 'CCB1';
        $this->posInterfaceCode = $credentials['pos_interface_code'] ?? $credentials['client_id'];
        $this->appCode = $credentials['app_code'] ?? 'POS-02';
    }

    /**
     * Get authentication token
     */
    public function getAuthToken()
    {
        try {
            $cacheKey = 'gallface_token_' . md5($this->clientId);
            
            // Check if token exists in cache
            if (Cache::has($cacheKey)) {
                return [
                    'success' => true,
                    'token' => Cache::get($cacheKey)
                ];
            }

            Log::info('Gallface: Requesting new token', [
                'token_url' => $this->tokenUrl,
                'client_id' => $this->clientId,
                'client_secret_length' => strlen($this->clientSecret)
            ]);

            // Ensure URL doesn't have trailing slash
            $tokenUrl = rtrim($this->tokenUrl, '/');

            $response = Http::timeout(30)
                ->asForm()
                ->post($tokenUrl, [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret
                ]);

            Log::info('Gallface: Token response received', [
                'status' => $response->status(),
                'response_body' => $response->body(),
                'headers' => $response->headers()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Handle different response formats
                $token = $data['access_token'] ?? $data['AccessToken'] ?? $data['token'] ?? null;
                $expiresIn = $data['expires_in'] ?? $data['ExpiresIn'] ?? $data['expiresIn'] ?? 3600;

                if ($token) {
                    // Cache token for expires_in - 120 seconds (2 minutes buffer)
                    $cacheTime = max(60, $expiresIn - 120); // At least 1 minute
                    Cache::put($cacheKey, $token, now()->addSeconds($cacheTime));

                    Log::info('Gallface: Token obtained successfully', [
                        'token_preview' => substr($token, 0, 20) . '...',
                        'expires_in' => $expiresIn
                    ]);

                    return [
                        'success' => true,
                        'token' => $token
                    ];
                }

                Log::error('Gallface: Token found in response but value is null', [
                    'response_data' => $data
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to obtain token: Token is null in response'
                ];
            }

            Log::error('Gallface: Token request failed', [
                'status' => $response->status(),
                'response' => $response->body(),
                'token_url' => $tokenUrl
            ]);

            return [
                'success' => false,
                'message' => 'Failed to obtain token: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Gallface: Token exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'Token error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test API connection
     */
    public function testConnection()
    {
        $tokenResult = $this->getAuthToken();
        
        if (!$tokenResult['success']) {
            return [
                'success' => false,
                'message' => 'Authentication failed: ' . $tokenResult['message']
            ];
        }

        return [
            'success' => true,
            'message' => 'Connection successful',
            'token_obtained' => true
        ];
    }

    /**
     * Sync sales data to Gallface MIMS API
     */
    public function syncSales($salesData, $locationId)
    {
        try {
            // Validate credentials
            if (empty($this->clientId) || empty($this->clientSecret)) {
                Log::error('Gallface: Missing credentials', [
                    'has_client_id' => !empty($this->clientId),
                    'has_client_secret' => !empty($this->clientSecret)
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Missing API credentials. Please configure Client ID and Client Secret.',
                    'records_synced' => 0
                ];
            }
            
            if (empty($this->productionUrl)) {
                Log::error('Gallface: Missing production URL');
                
                return [
                    'success' => false,
                    'message' => 'Missing Production URL. Please configure the API endpoint.',
                    'records_synced' => 0
                ];
            }
            
            $tokenResult = $this->getAuthToken();
            
            if (!$tokenResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Authentication failed',
                    'records_synced' => 0
                ];
            }

            $token = $tokenResult['token'];
            $batchCode = now()->timestamp; // Unix timestamp for BatchCode
            
            $posSales = [];
            foreach ($salesData as $sale) {
                // Double-check location_id matches to ensure we only sync this location's sales
                if (isset($sale->location_id) && $sale->location_id != $locationId) {
                    Log::warning('Gallface: Skipping sale from different location', [
                        'sale_location_id' => $sale->location_id,
                        'expected_location_id' => $locationId,
                        'invoice_no' => $sale->invoice_no ?? 'unknown'
                    ]);
                    continue;
                }
                
                $formattedSale = $this->formatSaleForGallface($sale);
                if ($formattedSale) {
                    $posSales[] = $formattedSale;
                }
            }

            if (empty($posSales)) {
                return [
                    'success' => false,
                    'message' => 'No valid sales data to sync',
                    'records_synced' => 0
                ];
            }

            $payload = [
                'AppCode' => $this->appCode,
                'PropertyCode' => $this->propertyCode,
                'ClientID' => $this->clientId,
                'ClientSecret' => $this->clientSecret,
                'POSInterfaceCode' => $this->posInterfaceCode,
                'BatchCode' => (string)$batchCode,
                'POSSALES' => $posSales  // Changed to uppercase to match API spec
            ];

            Log::info('Gallface: Syncing sales to MIMS', [
                'batch_code' => $batchCode,
                'records_count' => count($posSales),
                'location_id' => $locationId,
                'endpoint' => $this->productionUrl . '/api/possale/importpossaleswithitems',
                'payload_preview' => [
                    'AppCode' => $this->appCode,
                    'PropertyCode' => $this->propertyCode,
                    'ClientID' => $this->clientId,
                    'POSInterfaceCode' => $this->posInterfaceCode,
                    'BatchCode' => (string)$batchCode,
                    'sales_count' => count($posSales)
                ]
            ]);

            // Ensure production URL doesn't have trailing slash
            $baseUrl = rtrim($this->productionUrl, '/');
            
            // Validate URL
            if (empty($baseUrl)) {
                Log::error('Gallface: Production URL is empty', [
                    'production_url' => $this->productionUrl,
                    'client_id' => $this->clientId
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Production URL is not configured. Please set it in the integration settings.',
                    'records_synced' => 0
                ];
            }
            
            if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
                Log::error('Gallface: Invalid production URL format', [
                    'production_url' => $this->productionUrl
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Invalid production URL format: ' . $this->productionUrl,
                    'records_synced' => 0
                ];
            }
            
            $fullUrl = $baseUrl . '/api/possale/importpossaleswithitems';
            
            Log::info('Gallface: Calling API', [
                'url' => $fullUrl,
                'base_url' => $baseUrl,
                'has_token' => !empty($token),
                'property_code' => $this->propertyCode,
                'pos_interface_code' => $this->posInterfaceCode,
                'sales_count' => count($posSales)
            ]);
            
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ])
                ->post($fullUrl, $payload);

            if ($response->successful()) {
                $result = $response->json();
                
                Log::info('Gallface: Sync successful', [
                    'batch_code' => $batchCode,
                    'status_code' => $response->status(),
                    'response' => $result
                ]);

                // API might return different response structures
                $recordsImported = $result['recordsImported'] ?? $result['RecordsImported'] ?? $result['recordImported'] ?? count($posSales);

                return [
                    'success' => true,
                    'message' => 'Sales synced successfully to One Gallface Mall',
                    'records_synced' => $recordsImported,
                    'batch_code' => $batchCode,
                    'api_response' => $result
                ];
            }

            $errorMessage = 'HTTP ' . $response->status();
            $responseBody = $response->body();
            
            // Try to parse JSON error response
            try {
                $jsonError = $response->json();
                if (isset($jsonError['message'])) {
                    $errorMessage = $jsonError['message'];
                } elseif (isset($jsonError['error'])) {
                    $errorMessage = $jsonError['error'];
                } else {
                    $errorMessage .= ': ' . $responseBody;
                }
            } catch (\Exception $e) {
                $errorMessage .= ': ' . $responseBody;
            }

            Log::error('Gallface: Sync failed', [
                'status' => $response->status(),
                'response' => $responseBody,
                'error_message' => $errorMessage,
                'batch_code' => $batchCode
            ]);

            return [
                'success' => false,
                'message' => 'Sync failed: ' . $errorMessage,
                'records_synced' => 0
            ];

        } catch (\Exception $e) {
            Log::error('Gallface: Sync exception', [
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
     * Format sale data for Gallface API
     */
    private function formatSaleForGallface($sale)
    {
        try {
            $saleDate = Carbon::parse($sale->transaction_date ?? now());
            
            // Get line items
            $items = \DB::table('transaction_sell_lines')
                ->where('transaction_id', $sale->id)
                ->get();

            $formattedItems = [];
            foreach ($items as $item) {
                $formattedItems[] = [
                    'ItemDesc' => $item->product_name ?? 'Item',
                    'ItemAmt' => (float)number_format((float)$item->unit_price_inc_tax, 2, '.', ''),
                    'ItemDiscountAmt' => (float)number_format((float)($item->line_discount_amount ?? 0), 2, '.', '')  // Fixed typo: ItemDiscountAmt not ItemDiscoumtAmt
                ];
            }

            // Determine sales type
            $salesType = 'Sales';
            if ($sale->type === 'sell_return') {
                $salesType = 'Return';
            }

            // Calculate amounts
            $totalB4Tax = (float)($sale->final_total - $sale->tax_amount);
            $totalAfterTax = (float)$sale->final_total;
            $taxRate = $sale->tax_amount > 0 ? (($sale->tax_amount / $totalB4Tax) * 100) : 0;

            // Get payment method
            $paymentMethod = 'Cash';
            $payment = \DB::table('transaction_payments')
                ->where('transaction_id', $sale->id)
                ->first();
            
            if ($payment) {
                $paymentMethod = ucfirst($payment->method ?? 'Cash');
            }

            return [
                'PropertyCode' => $this->propertyCode,
                'POSInterfaceCode' => $this->posInterfaceCode,
                'ReceiptDate' => $saleDate->format('d/m/Y'),  // dd/mm/yyyy format
                'ReceiptTime' => $saleDate->format('H:i:s'),  // HH:MM:SS format
                'ReceiptNo' => $sale->invoice_no,
                'NoOfItems' => count($formattedItems),
                'SalesCurrency' => 'LKR',
                'TotalSalesAmtB4Tax' => (float)number_format($totalB4Tax, 2, '.', ''),
                'TotalSalesAmtAfterTax' => (float)number_format($totalAfterTax, 2, '.', ''),
                'SalesTaxRate' => (float)number_format($taxRate, 2, '.', ''),
                'ServiceChargeAmt' => 0.00,
                'PaymentAmt' => (float)number_format($totalAfterTax, 2, '.', ''),
                'PaymentCurrency' => 'LKR',
                'PaymentMethod' => $paymentMethod,
                'SalesType' => $salesType,
                'Items' => $formattedItems
            ];

        } catch (\Exception $e) {
            Log::error('Gallface: Format sale error: ' . $e->getMessage(), [
                'sale_id' => $sale->id ?? 'unknown'
            ]);
            return null;
        }
    }
}
