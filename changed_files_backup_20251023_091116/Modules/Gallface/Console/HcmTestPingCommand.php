<?php

namespace Modules\Gallface\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HcmTestPingCommand extends Command
{
    protected $signature = 'hcm:test-ping {--tenant-id=EXT-TEST-01} {--tenant-secret=UH2S&%z@} {--api-url=https://trms-api.azurewebsites.net}';
    protected $description = 'Test HCM API authentication and ping functionality';

    public function handle()
    {
        $tenantId = $this->option('tenant-id');
        $tenantSecret = $this->option('tenant-secret');
        $apiUrl = $this->option('api-url');

        $this->info("Testing HCM API Integration");
        $this->info("API URL: {$apiUrl}");
        $this->info("Tenant ID: {$tenantId}");
        $this->line("----------------------------------------");

        // Step 1: Test Authentication
        $this->info("\n1. Testing Authentication...");
        $authResult = $this->testAuthentication($apiUrl, $tenantId, $tenantSecret);

        if (!$authResult['success']) {
            $this->error("✗ Authentication Failed");
            $this->error("Status: " . $authResult['status_code']);
            $this->error("Error: " . json_encode($authResult['error'], JSON_PRETTY_PRINT));
            return 1;
        }

        $this->info("✓ Authentication Successful");
        $this->info("Token Type: " . $authResult['token_type']);
        $this->info("Expires In: " . $authResult['expires_in'] . " seconds");
        $this->info("Token Preview: " . substr($authResult['access_token'], 0, 50) . "...");

        // Step 2: Test Ping (if endpoint exists)
        $token = $authResult['access_token'];
        $this->info("\n2. Testing Ping Endpoint...");
        $pingResult = $this->testPing($apiUrl, $token, $tenantId);

        if ($pingResult['success']) {
            $this->info("✓ Ping Successful");
            $this->info("Response: " . json_encode($pingResult['response'], JSON_PRETTY_PRINT));
        } else {
            $this->warn("⚠ Ping endpoint not available or failed");
            $this->warn("Status: " . $pingResult['status_code']);
        }

        // Step 3: Test Invoice Submission
        $this->info("\n3. Testing Invoice Submission...");
        $invoiceResult = $this->testInvoiceSubmission($apiUrl, $token, $tenantId);

        if ($invoiceResult['success']) {
            $this->info("✓ Invoice Submission Successful");
            $this->info("Response: " . json_encode($invoiceResult['response'], JSON_PRETTY_PRINT));
        } else {
            $this->error("✗ Invoice Submission Failed");
            $this->error("Status: " . $invoiceResult['status_code']);
            $this->error("Error: " . json_encode($invoiceResult['error'], JSON_PRETTY_PRINT));
        }

        $this->line("\n----------------------------------------");
        $this->info("Test Complete!");
        return 0;
    }

    private function testAuthentication($apiUrl, $tenantId, $tenantSecret)
    {
        try {
            $requestData = [
                'tenant_id' => $tenantId,
                'tenant_secret' => $tenantSecret,
                'grant_type' => 'client_credentials'
            ];

            Log::info('HCM Test Auth Request', [
                'url' => $apiUrl . '/api/token',
                'data' => $requestData
            ]);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json'
                ])
                ->post($apiUrl . '/api/token', $requestData);

            $responseData = $response->json();

            Log::info('HCM Test Auth Response', [
                'status' => $response->status(),
                'body' => $responseData
            ]);

            if ($response->successful() && isset($responseData['access_token'])) {
                return [
                    'success' => true,
                    'status_code' => $response->status(),
                    'access_token' => $responseData['access_token'],
                    'token_type' => $responseData['token_type'] ?? 'Bearer',
                    'expires_in' => $responseData['expires_in'] ?? 480
                ];
            }

            return [
                'success' => false,
                'status_code' => $response->status(),
                'error' => $responseData
            ];

        } catch (\Exception $e) {
            Log::error('HCM Test Auth Exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'status_code' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    private function testPing($apiUrl, $token, $tenantId)
    {
        try {
            $pingData = [
                'tenantId' => $tenantId,
                'posId' => 'POS-TEST-01',
                'stallNo' => 'STALL-01',
                'timestamp' => Carbon::now()->format('d/m/Y H:i:s')
            ];

            Log::info('HCM Test Ping Request', [
                'url' => $apiUrl . '/api/ping',
                'data' => $pingData
            ]);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ])
                ->post($apiUrl . '/api/ping', $pingData);

            $responseData = $response->json();

            Log::info('HCM Test Ping Response', [
                'status' => $response->status(),
                'body' => $responseData
            ]);

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response' => $responseData
            ];

        } catch (\Exception $e) {
            Log::error('HCM Test Ping Exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'status_code' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    private function testInvoiceSubmission($apiUrl, $token, $tenantId)
    {
        try {
            // Create test invoice matching Postman example
            $invoiceData = [
                'tenantId' => $tenantId,
                'posId' => 'POS-TEST-01',
                'stallNo' => 'STALL-01',
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

            Log::info('HCM Test Invoice Request', [
                'url' => $apiUrl . '/api/invoices',
                'data' => $invoiceData
            ]);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ])
                ->post($apiUrl . '/api/invoices', $invoiceData);

            $responseData = $response->json();

            Log::info('HCM Test Invoice Response', [
                'status' => $response->status(),
                'body' => $responseData
            ]);

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response' => $responseData,
                'invoice_no' => $invoiceData['invoiceNo']
            ];

        } catch (\Exception $e) {
            Log::error('HCM Test Invoice Exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'status_code' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
}
