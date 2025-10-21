<?php

namespace Modules\Gallface\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HcmTestController extends Controller
{
    /**
     * Test HCM API authentication and invoice validation
     */
    public function testHcmApi(Request $request)
    {
        $results = [];

        // Get credentials from request or use defaults
        $apiUrl = $request->input('api_url', 'https://trms-api.azurewebsites.net');
        $tenantId = $request->input('tenant_id');
        $tenantSecret = $request->input('tenant_secret');
        $posId = $request->input('pos_id', 'POS-001');

        if (!$tenantId || !$tenantSecret) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide tenant_id and tenant_secret'
            ], 400);
        }

        // Step 1: Test Authentication
        $results['authentication'] = $this->testAuthentication($apiUrl, $tenantId, $tenantSecret);

        if (!$results['authentication']['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'details' => $results
            ]);
        }

        // Step 2: Test Invoice Validation
        $token = $results['authentication']['data']['access_token'];
        $results['invoice_validation'] = $this->testInvoiceValidation($apiUrl, $token, $tenantId, $posId);

        // Summary
        $summary = [
            'success' => $results['authentication']['success'] && $results['invoice_validation']['success'],
            'message' => 'HCM API Test Complete',
            'results' => $results,
            'summary' => [
                'authentication_status' => $results['authentication']['success'] ? 'SUCCESS' : 'FAILED',
                'token_received' => $results['authentication']['data']['access_token'] ?? 'N/A',
                'token_expires_in' => $results['authentication']['data']['expires_in'] ?? 'N/A',
                'invoice_validation_status' => $results['invoice_validation']['success'] ? 'SUCCESS' : 'FAILED',
                'invoice_response' => $results['invoice_validation']['message'] ?? 'N/A'
            ]
        ];

        return response()->json($summary);
    }

    /**
     * Test authentication endpoint
     */
    private function testAuthentication($apiUrl, $tenantId, $tenantSecret)
    {
        try {
            $requestData = [
                'grant_type' => 'client_credentials',
                'tenant_id' => $tenantId,
                'tenant_secret' => $tenantSecret
            ];

            Log::info('HCM Auth Request', [
                'url' => $apiUrl . '/api/token',
                'data' => $requestData
            ]);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json'
                ])
                ->post($apiUrl . '/api/token', $requestData);

            $responseData = $response->json();

            Log::info('HCM Auth Response', [
                'status' => $response->status(),
                'body' => $responseData
            ]);

            if ($response->successful() && isset($responseData['access_token'])) {
                return [
                    'success' => true,
                    'message' => 'Authentication successful',
                    'status_code' => $response->status(),
                    'request' => $requestData,
                    'data' => [
                        'access_token' => $responseData['access_token'],
                        'token_type' => $responseData['token_type'] ?? 'Bearer',
                        'expires_in' => $responseData['expires_in'] ?? 480
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'Authentication failed',
                'status_code' => $response->status(),
                'request' => $requestData,
                'response' => $responseData,
                'error' => $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('HCM Auth Exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Authentication exception: ' . $e->getMessage(),
                'exception' => $e->getMessage()
            ];
        }
    }

    /**
     * Test invoice validation endpoint
     */
    private function testInvoiceValidation($apiUrl, $token, $tenantId, $posId)
    {
        try {
            // Create test invoice data according to documentation
            $invoiceData = [
                'tenantId' => $tenantId,
                'posId' => $posId,
                'cashierId' => 'CASH-001',
                'customerMobileNo' => '0771234567',
                'invoiceType' => 'Sale',
                'invoiceNo' => 'TEST-' . time(),
                'invoiceDate' => Carbon::now()->format('d/m/Y H:i:s'),
                'currencyCode' => 'LKR',
                'currencyRate' => 1.0000,
                'totalInvoice' => 10750.00,
                'totalTax' => 444.44,
                'totalDiscount' => 500.00,
                'totalGiftVoucherSale' => 5000.00,
                'totalGiftVoucherTax' => 351.85,
                'totalGiftVoucherDiscount' => 250.00,
                'paidByCash' => 8000.00,
                'paidByCard' => 1000.00,
                'cardBank' => '7010',
                'cardCategory' => 'Debit',
                'cardType' => 'VISA',
                'GiftVoucherBurn' => 1000.00,
                'hcmLoyalty' => 500.00,
                'tenantLoyalty' => 250.00,
                'creditNotes' => 0.00,
                'otherPayments' => 0.00
            ];

            Log::info('HCM Invoice Validation Request', [
                'url' => $apiUrl . '/api/validate',
                'data' => $invoiceData
            ]);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ])
                ->post($apiUrl . '/api/validate', $invoiceData);

            $responseData = $response->json();

            Log::info('HCM Invoice Validation Response', [
                'status' => $response->status(),
                'body' => $responseData
            ]);

            return [
                'success' => $response->successful(),
                'message' => $response->successful() ? 'Invoice validation successful' : 'Invoice validation failed',
                'status_code' => $response->status(),
                'request' => $invoiceData,
                'response' => $responseData,
                'raw_response' => $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('HCM Invoice Validation Exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Invoice validation exception: ' . $e->getMessage(),
                'exception' => $e->getMessage()
            ];
        }
    }

    /**
     * Test sync with dummy invoices for all invoice types
     */
    public function testAllInvoiceTypes(Request $request)
    {
        $apiUrl = $request->input('api_url', 'https://trms-api.azurewebsites.net');
        $tenantId = $request->input('tenant_id');
        $tenantSecret = $request->input('tenant_secret');
        $posId = $request->input('pos_id', 'POS-001');

        if (!$tenantId || !$tenantSecret) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide tenant_id and tenant_secret'
            ], 400);
        }

        // Get authentication token first
        $authResult = $this->testAuthentication($apiUrl, $tenantId, $tenantSecret);
        
        if (!$authResult['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'details' => $authResult
            ]);
        }

        $token = $authResult['data']['access_token'];
        $results = [];

        // Test each invoice type
        $invoiceTypes = ['Sale', 'Return', 'Void', 'Refund', 'Exchange'];

        foreach ($invoiceTypes as $type) {
            $results[$type] = $this->testInvoiceType($apiUrl, $token, $tenantId, $posId, $type);
        }

        // Summary
        $successCount = 0;
        $failedCount = 0;
        
        foreach ($results as $type => $result) {
            if ($result['success']) {
                $successCount++;
            } else {
                $failedCount++;
            }
        }

        return response()->json([
            'success' => $successCount > 0,
            'message' => "Tested {$successCount} invoice types successfully" . ($failedCount > 0 ? ", {$failedCount} failed" : ''),
            'summary' => [
                'total_types_tested' => count($invoiceTypes),
                'successful' => $successCount,
                'failed' => $failedCount
            ],
            'results' => $results
        ]);
    }

    /**
     * Test a specific invoice type
     */
    private function testInvoiceType($apiUrl, $token, $tenantId, $posId, $invoiceType)
    {
        try {
            // Create test invoice data based on type
            $baseInvoiceNo = 'TEST-' . strtoupper($invoiceType) . '-' . time();
            
            // Adjust amounts based on invoice type
            $totalInvoice = $invoiceType === 'Return' ? -10750.00 : 10750.00;
            $totalTax = $invoiceType === 'Return' ? -444.44 : 444.44;
            $totalDiscount = $invoiceType === 'Return' ? -500.00 : 500.00;
            
            $invoiceData = [
                'tenantId' => substr($tenantId, 0, 15),
                'posId' => substr($posId, 0, 25),
                'cashierId' => 'CASH-TEST-01',
                'customerMobileNo' => '0771234567',
                'invoiceType' => $invoiceType,
                'invoiceNo' => $baseInvoiceNo,
                'invoiceDate' => Carbon::now()->format('d/m/Y H:i:s'),
                'currencyCode' => 'LKR',
                'currencyRate' => 1.0000,
                'totalInvoice' => $totalInvoice,
                'totalTax' => $totalTax,
                'totalDiscount' => $totalDiscount,
                'totalGiftVoucherSale' => 5000.00,
                'totalGiftVoucherTax' => 351.85,
                'totalGiftVoucherDiscount' => 250.00,
                'paidByCash' => 8000.00,
                'paidByCard' => 1000.00,
                'cardBank' => '7010',
                'cardCategory' => 'Debit',
                'cardType' => 'VISA',
                'GiftVoucherBurn' => 1000.00,
                'hcmLoyalty' => 500.00,
                'tenantLoyalty' => 250.00,
                'creditNotes' => 0.00,
                'otherPayments' => 0.00
            ];

            Log::info("Testing Invoice Type: {$invoiceType}", [
                'url' => $apiUrl . '/api/validate',
                'invoice_no' => $baseInvoiceNo,
                'data' => $invoiceData
            ]);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ])
                ->post($apiUrl . '/api/validate', $invoiceData);

            $responseData = $response->json();

            Log::info("Invoice Type {$invoiceType} Response", [
                'status' => $response->status(),
                'body' => $responseData
            ]);

            return [
                'success' => $response->successful(),
                'invoice_type' => $invoiceType,
                'message' => $response->successful() ? "✓ {$invoiceType} validation successful" : "✗ {$invoiceType} validation failed",
                'status_code' => $response->status(),
                'request' => $invoiceData,
                'response' => $responseData,
                'raw_response' => $response->body()
            ];

        } catch (\Exception $e) {
            Log::error("Invoice Type {$invoiceType} Exception", ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'invoice_type' => $invoiceType,
                'message' => "✗ {$invoiceType} exception: " . $e->getMessage(),
                'exception' => $e->getMessage()
            ];
        }
    }
}