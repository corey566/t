<?php

namespace Modules\Gallface\Console;

use Illuminate\Console\Command;
use Modules\Gallface\Services\HcmApiService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HcmValidationTestCommand extends Command
{
    protected $signature = 'hcm:validate-scenarios {--tenant-id=} {--tenant-secret=} {--pos-id=} {--stall-no=} {--api-url=https://trms-api.azurewebsites.net}';
    protected $description = 'Validate all HCM integration scenarios comprehensively';

    public function handle()
    {
        $tenantId = $this->option('tenant-id');
        $tenantSecret = $this->option('tenant-secret');
        $posId = $this->option('pos-id');
        $stallNo = $this->option('stall-no');
        $apiUrl = $this->option('api-url');

        if (!$tenantId || !$tenantSecret || !$posId || !$stallNo) {
            $this->error('Missing required credentials. Please provide: --tenant-id, --tenant-secret, --pos-id, --stall-no');
            return 1;
        }

        $this->info('╔════════════════════════════════════════════════════════════════╗');
        $this->info('║       HCM Integration - Comprehensive Scenario Validation      ║');
        $this->info('╚════════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $credentials = [
            'api_url' => $apiUrl,
            'username' => $tenantId,
            'password' => $tenantSecret,
            'stall_no' => $stallNo,
            'pos_id' => $posId,
        ];

        $apiService = new HcmApiService($credentials);
        $results = [];

        // 1. Card Sales
        $this->info('1️⃣  Testing Card Sales...');
        $results['card_sale'] = $this->testCardSale($apiService, $tenantId, $posId, $stallNo);
        $this->displayResult($results['card_sale']);

        // 2. Cash Sales
        $this->info('2️⃣  Testing Cash Sales...');
        $results['cash_sale'] = $this->testCashSale($apiService, $tenantId, $posId, $stallNo);
        $this->displayResult($results['cash_sale']);

        // 3. Card + Cash Mixed Transaction
        $this->info('3️⃣  Testing Card + Cash Mixed Transaction...');
        $results['mixed_payment'] = $this->testMixedPayment($apiService, $tenantId, $posId, $stallNo);
        $this->displayResult($results['mixed_payment']);

        // 4. Return Transaction
        $this->info('4️⃣  Testing Return Transaction...');
        $results['return'] = $this->testReturn($apiService, $tenantId, $posId, $stallNo);
        $this->displayResult($results['return']);

        // 5. Exchange Transaction
        $this->info('5️⃣  Testing Exchange Transaction...');
        $results['exchange'] = $this->testExchange($apiService, $tenantId, $posId, $stallNo);
        $this->displayResult($results['exchange']);

        // 6. Refund Transaction
        $this->info('6️⃣  Testing Refund Transaction...');
        $results['refund'] = $this->testRefund($apiService, $tenantId, $posId, $stallNo);
        $this->displayResult($results['refund']);

        // 7. Void Transaction
        $this->info('7️⃣  Testing Void Transaction...');
        $results['void'] = $this->testVoid($apiService, $tenantId, $posId, $stallNo);
        $this->displayResult($results['void']);

        // 8. HCM Voucher Redemption
        $this->info('8️⃣  Testing HCM Voucher Redemption...');
        $results['hcm_voucher'] = $this->testHcmVoucherRedemption($apiService, $tenantId, $posId, $stallNo);
        $this->displayResult($results['hcm_voucher']);

        // 9. Discount Application
        $this->info('9️⃣  Testing Discount Application...');
        $results['discount'] = $this->testDiscounts($apiService, $tenantId, $posId, $stallNo);
        $this->displayResult($results['discount']);

        // 10. Tenant Gift Voucher Sale
        $this->info('🔟 Testing Tenant Gift Voucher Sale...');
        $results['gift_voucher_sale'] = $this->testGiftVoucherSale($apiService, $tenantId, $posId, $stallNo);
        $this->displayResult($results['gift_voucher_sale']);

        // 11. Tenant Gift Voucher Redemption
        $this->info('1️⃣1️⃣  Testing Tenant Gift Voucher Redemption...');
        $results['gift_voucher_redeem'] = $this->testGiftVoucherRedemption($apiService, $tenantId, $posId, $stallNo);
        $this->displayResult($results['gift_voucher_redeem']);

        // 12. POS Terminal Ping
        $this->info('1️⃣2️⃣  Testing POS Terminal Ping...');
        $results['ping'] = $this->testPing($apiService);
        $this->displayResult($results['ping']);

        // 13. Loyalty Mobile Number
        $this->info('1️⃣3️⃣  Testing Loyalty with Mobile Number...');
        $results['loyalty_mobile'] = $this->testLoyaltyMobile($apiService, $tenantId, $posId, $stallNo);
        $this->displayResult($results['loyalty_mobile']);

        $this->newLine(2);
        $this->showSummary($results);

        return 0;
    }

    private function testCardSale($apiService, $tenantId, $posId, $stallNo)
    {
        $invoice = $this->createInvoice($tenantId, $posId, $stallNo, [
            'invoiceType' => 'Sale',
            'totalInvoice' => 5000.00,
            'paidByCard' => 5000.00,
            'paidByCash' => 0.00,
            'cardBank' => '7010',
            'cardCategory' => 'Credit',
            'cardType' => 'VISA',
        ]);

        return $this->submitInvoice($apiService, $invoice, 'Card Sale');
    }

    private function testCashSale($apiService, $tenantId, $posId, $stallNo)
    {
        $invoice = $this->createInvoice($tenantId, $posId, $stallNo, [
            'invoiceType' => 'Sale',
            'totalInvoice' => 3000.00,
            'paidByCash' => 3000.00,
            'paidByCard' => 0.00,
        ]);

        return $this->submitInvoice($apiService, $invoice, 'Cash Sale');
    }

    private function testMixedPayment($apiService, $tenantId, $posId, $stallNo)
    {
        $invoice = $this->createInvoice($tenantId, $posId, $stallNo, [
            'invoiceType' => 'Sale',
            'totalInvoice' => 8000.00,
            'paidByCash' => 5000.00,
            'paidByCard' => 3000.00,
            'cardBank' => '7010',
            'cardCategory' => 'Debit',
            'cardType' => 'MASTERCARD',
        ]);

        return $this->submitInvoice($apiService, $invoice, 'Mixed Payment (Card + Cash)');
    }

    private function testReturn($apiService, $tenantId, $posId, $stallNo)
    {
        $invoice = $this->createInvoice($tenantId, $posId, $stallNo, [
            'invoiceType' => 'Return',
            'totalInvoice' => -2000.00,
            'totalTax' => -200.00,
            'totalDiscount' => 0.00,
            'paidByCash' => -2000.00,
        ]);

        return $this->submitInvoice($apiService, $invoice, 'Return Transaction');
    }

    private function testExchange($apiService, $tenantId, $posId, $stallNo)
    {
        $invoice = $this->createInvoice($tenantId, $posId, $stallNo, [
            'invoiceType' => 'Exchange',
            'totalInvoice' => 500.00,
            'paidByCash' => 500.00,
        ]);

        return $this->submitInvoice($apiService, $invoice, 'Exchange Transaction');
    }

    private function testRefund($apiService, $tenantId, $posId, $stallNo)
    {
        $invoice = $this->createInvoice($tenantId, $posId, $stallNo, [
            'invoiceType' => 'Refund',
            'totalInvoice' => -1500.00,
            'paidByCard' => -1500.00,
        ]);

        return $this->submitInvoice($apiService, $invoice, 'Refund Transaction');
    }

    private function testVoid($apiService, $tenantId, $posId, $stallNo)
    {
        $invoice = $this->createInvoice($tenantId, $posId, $stallNo, [
            'invoiceType' => 'Void',
            'totalInvoice' => 0.00,
            'paidByCash' => 0.00,
        ]);

        return $this->submitInvoice($apiService, $invoice, 'Void Transaction');
    }

    private function testHcmVoucherRedemption($apiService, $tenantId, $posId, $stallNo)
    {
        $invoice = $this->createInvoice($tenantId, $posId, $stallNo, [
            'invoiceType' => 'Sale',
            'totalInvoice' => 6000.00,
            'paidByCash' => 4000.00,
            'hcmLoyalty' => 2000.00,
            'customerMobileNo' => '0771234567',
        ]);

        return $this->submitInvoice($apiService, $invoice, 'HCM Voucher Redemption');
    }

    private function testDiscounts($apiService, $tenantId, $posId, $stallNo)
    {
        $invoice = $this->createInvoice($tenantId, $posId, $stallNo, [
            'invoiceType' => 'Sale',
            'totalInvoice' => 4500.00,
            'totalDiscount' => 500.00,
            'paidByCash' => 4500.00,
        ]);

        return $this->submitInvoice($apiService, $invoice, 'Sale with Discount');
    }

    private function testGiftVoucherSale($apiService, $tenantId, $posId, $stallNo)
    {
        $invoice = $this->createInvoice($tenantId, $posId, $stallNo, [
            'invoiceType' => 'Sale',
            'totalInvoice' => 5500.00,
            'totalGiftVoucherSale' => 5000.00,
            'totalGiftVoucherTax' => 350.00,
            'totalGiftVoucherDiscount' => 250.00,
            'paidByCash' => 5500.00,
        ]);

        return $this->submitInvoice($apiService, $invoice, 'Gift Voucher Sale');
    }

    private function testGiftVoucherRedemption($apiService, $tenantId, $posId, $stallNo)
    {
        $invoice = $this->createInvoice($tenantId, $posId, $stallNo, [
            'invoiceType' => 'Sale',
            'totalInvoice' => 7000.00,
            'GiftVoucherBurn' => 1500.00,
            'paidByCash' => 5500.00,
        ]);

        return $this->submitInvoice($apiService, $invoice, 'Gift Voucher Redemption');
    }

    private function testPing($apiService)
    {
        try {
            $result = $apiService->sendPing(1, 'test_user', '127.0.0.1');
            return [
                'success' => $result['success'],
                'scenario' => 'POS Terminal Ping',
                'message' => $result['message'],
                'details' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'scenario' => 'POS Terminal Ping',
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }

    private function testLoyaltyMobile($apiService, $tenantId, $posId, $stallNo)
    {
        $invoice = $this->createInvoice($tenantId, $posId, $stallNo, [
            'invoiceType' => 'Sale',
            'totalInvoice' => 4000.00,
            'customerMobileNo' => '0771234567',
            'tenantLoyalty' => 500.00,
            'paidByCash' => 3500.00,
        ]);

        return $this->submitInvoice($apiService, $invoice, 'Loyalty with Mobile Number');
    }

    private function createInvoice($tenantId, $posId, $stallNo, $overrides = [])
    {
        $defaults = [
            'tenantId' => substr($tenantId, 0, 15),
            'posId' => substr($posId, 0, 25),
            'stallNo' => substr($stallNo, 0, 10),
            'cashierId' => 'CASH-TEST-01',
            'customerMobileNo' => '',
            'invoiceType' => 'Sale',
            'invoiceNo' => 'TEST-' . time() . '-' . rand(1000, 9999),
            'invoiceDate' => Carbon::now()->format('d/m/Y H:i:s'),
            'currencyCode' => 'LKR',
            'currencyRate' => 1.0000,
            'totalInvoice' => 5000.00,
            'totalTax' => 400.00,
            'totalDiscount' => 0.00,
            'totalGiftVoucherSale' => 0.00,
            'totalGiftVoucherTax' => 0.00,
            'totalGiftVoucherDiscount' => 0.00,
            'paidByCash' => 0.00,
            'paidByCard' => 0.00,
            'cardBank' => '',
            'cardCategory' => '',
            'cardType' => '',
            'GiftVoucherBurn' => 0.00,
            'hcmLoyalty' => 0.00,
            'tenantLoyalty' => 0.00,
            'creditNotes' => 0.00,
            'havelockCityVoucher' => 0.00,
            'otherPayments' => 0.00,
        ];

        return array_merge($defaults, $overrides);
    }

    private function submitInvoice($apiService, $invoice, $scenario)
    {
        try {
            $saleData = [(object)$invoice];
            $result = $apiService->syncSales($saleData, 1);

            return [
                'success' => $result['success'],
                'scenario' => $scenario,
                'invoice_no' => $invoice['invoiceNo'],
                'message' => $result['message'],
                'details' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'scenario' => $scenario,
                'invoice_no' => $invoice['invoiceNo'],
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }

    private function displayResult($result)
    {
        if ($result['success']) {
            $this->info('   ✅ ' . $result['scenario'] . ' - PASSED');
            if (isset($result['invoice_no'])) {
                $this->line('      Invoice: ' . $result['invoice_no']);
            }
        } else {
            $this->error('   ❌ ' . $result['scenario'] . ' - FAILED');
            $this->line('      Error: ' . $result['message']);
        }
        $this->newLine();
    }

    private function showSummary($results)
    {
        $total = count($results);
        $passed = count(array_filter($results, fn($r) => $r['success']));
        $failed = $total - $passed;

        $this->info('╔════════════════════════════════════════════════════════════════╗');
        $this->info('║                     VALIDATION SUMMARY                         ║');
        $this->info('╠════════════════════════════════════════════════════════════════╣');
        $this->info(sprintf('║  Total Scenarios: %d                                           ║', $total));
        $this->info(sprintf('║  Passed: %d                                                    ║', $passed));
        $this->info(sprintf('║  Failed: %d                                                    ║', $failed));
        $this->info('╚════════════════════════════════════════════════════════════════╝');

        if ($failed > 0) {
            $this->newLine();
            $this->warn('Failed Scenarios:');
            foreach ($results as $result) {
                if (!$result['success']) {
                    $this->line('  • ' . $result['scenario'] . ': ' . $result['message']);
                }
            }
        }

        $this->newLine();
        $this->info('📊 Day-End Excel Report Requirement:');
        $this->line('   The system should generate Excel reports containing:');
        $this->line('   - All transaction types (Sales, Returns, Exchanges, Refunds, Voids)');
        $this->line('   - Payment breakdowns (Cash, Card, Mixed)');
        $this->line('   - Voucher redemptions (HCM & Tenant)');
        $this->line('   - Discount applications');
        $this->line('   - Gift voucher sales and redemptions');
        $this->line('   - Loyalty transactions with mobile numbers');
    }
}
