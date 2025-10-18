
<?php

namespace Modules\Hcm\Utils;

use App\Transaction;
use App\TransactionSellLine;
use Modules\Hcm\Entities\HcmTenantConfig;
use Modules\Hcm\Entities\HcmInvoiceLog;
use Modules\Hcm\Entities\HcmPingLog;
use Modules\Hcm\Entities\HcmSyncLog;
use GuzzleHttp\Client;
use Carbon\Carbon;

class HcmUtil
{
    /**
     * Test connection with HCM API
     */
    public function testConnection($business_id, $location_id)
    {
        $config = HcmTenantConfig::where('business_id', $business_id)
                                ->where('location_id', $location_id)
                                ->first();

        if (!$config) {
            throw new \Exception('Tenant configuration not found for this location');
        }

        try {
            $token = $this->getAuthToken($config);
            
            if ($token) {
                return [
                    'success' => true,
                    'msg' => 'Connection successful',
                    'token' => substr($token, 0, 10) . '...'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'msg' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get authentication token
     */
    public function getAuthToken($config)
    {
        $client = new Client();
        
        $response = $client->post($config->api_url . '/api/token', [
            'json' => [
                'grant_type' => 'test_credentials',
                'tenant_id' => $config->tenant_id,
                'tenant_secret' => $config->tenant_secret
            ],
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        
        if (isset($data['access_token'])) {
            return $data['access_token'];
        }

        throw new \Exception('Invalid response from authentication endpoint');
    }

    /**
     * Sync invoices with HCM
     */
    public function syncInvoices($business_id, $user_id)
    {
        $synced_count = 0;
        
        // Get active tenant configs
        $configs = HcmTenantConfig::where('business_id', $business_id)
                                 ->where('active', 1)
                                 ->get();

        foreach ($configs as $config) {
            // Get pending transactions for this location
            $transactions = Transaction::where('business_id', $business_id)
                                     ->where('location_id', $config->location_id)
                                     ->where('type', 'sell')
                                     ->where('status', 'final')
                                     ->whereDoesntHave('hcmInvoiceLog', function($query) {
                                         $query->where('status', 'success');
                                     })
                                     ->limit(50) // Process in batches
                                     ->get();

            foreach ($transactions as $transaction) {
                try {
                    $this->syncSingleInvoice($transaction, $config, $user_id);
                    $synced_count++;
                } catch (\Exception $e) {
                    // Log error but continue with next invoice
                    \Log::error('HCM Sync Error: ' . $e->getMessage());
                }
            }
        }

        // Create sync log
        $this->createSyncLog($business_id, $user_id, 'invoices', 'synced', ['count' => $synced_count]);

        return ['synced_count' => $synced_count];
    }

    /**
     * Sync single invoice
     */
    public function syncSingleInvoice($transaction, $config, $user_id)
    {
        // Get or create invoice log
        $invoice_log = HcmInvoiceLog::firstOrCreate([
            'business_id' => $transaction->business_id,
            'location_id' => $transaction->location_id,
            'transaction_id' => $transaction->id,
            'invoice_no' => $transaction->invoice_no,
        ]);

        if ($invoice_log->status == 'success') {
            return; // Already synced
        }

        try {
            // Get auth token
            $token = $this->getAuthToken($config);

            // Prepare invoice data
            $invoice_data = $this->prepareInvoiceData($transaction, $config);

            // Send to HCM API
            $client = new Client();
            $response = $client->post($config->api_url . '/api/validate', [
                'json' => $invoice_data,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ]
            ]);

            $response_data = json_decode($response->getBody(), true);

            // Update invoice log
            $invoice_log->update([
                'status' => 'success',
                'request_data' => json_encode($invoice_data),
                'response_data' => json_encode($response_data),
                'response_message' => 'Success',
                'synced_at' => now(),
            ]);

        } catch (\Exception $e) {
            // Update invoice log with error
            $invoice_log->update([
                'status' => 'failed',
                'response_message' => $e->getMessage(),
                'retry_count' => $invoice_log->retry_count + 1,
                'last_retry_at' => now(),
            ]);

            throw $e;
        }
    }

    /**
     * Prepare invoice data for HCM API
     */
    protected function prepareInvoiceData($transaction, $config)
    {
        // Calculate totals
        $sell_lines = TransactionSellLine::where('transaction_id', $transaction->id)->get();
        
        $total_gift_voucher_sale = 0;
        $total_gift_voucher_tax = 0;
        $total_gift_voucher_discount = 0;

        // Check for gift voucher products (you may need to adjust this logic)
        foreach ($sell_lines as $line) {
            if (strpos(strtolower($line->product->name), 'gift') !== false) {
                $total_gift_voucher_sale += $line->unit_price_inc_tax * $line->quantity;
                $total_gift_voucher_tax += $line->item_tax * $line->quantity;
                $total_gift_voucher_discount += $line->line_discount_amount;
            }
        }

        // Get payment details
        $payments = $transaction->payment_lines;
        $paid_by_cash = 0;
        $paid_by_card = 0;
        $card_bank = '';
        $card_category = '';
        $card_type = '';

        foreach ($payments as $payment) {
            if ($payment->method == 'cash') {
                $paid_by_cash += $payment->amount;
            } elseif ($payment->method == 'card') {
                $paid_by_card += $payment->amount;
                $card_bank = $payment->card_transaction_number ?? '7010'; // Default bank code
                $card_category = 'Debit'; // You may need to store this info
                $card_type = $payment->card_type ?? 'VISA';
            }
        }

        return [
            'tenantId' => $config->tenant_id,
            'posId' => $config->pos_id,
            'cashierId' => $transaction->created_by,
            'customerMobileNo' => $transaction->contact->mobile ?? '',
            'invoiceType' => 'Sale',
            'invoiceNo' => $transaction->invoice_no,
            'invoiceDate' => $transaction->transaction_date->format('d/m/Y H:i:s'),
            'currencyCode' => 'LKR',
            'currencyRate' => 1.0000,
            'totalInvoice' => (float) $transaction->final_total,
            'totalTax' => (float) $transaction->tax_amount,
            'totalDiscount' => (float) $transaction->discount_amount,
            'totalGiftVoucherSale' => (float) $total_gift_voucher_sale,
            'totalGiftVoucherTax' => (float) $total_gift_voucher_tax,
            'totalGiftVoucherDiscount' => (float) $total_gift_voucher_discount,
            'paidByCash' => (float) $paid_by_cash,
            'paidByCard' => (float) $paid_by_card,
            'cardBank' => $card_bank,
            'cardCategory' => $card_category,
            'cardType' => $card_type,
            'GiftVoucherBurn' => 0.00, // You may need to implement this
            'hcmLoyalty' => 0.00, // You may need to implement this
            'tenantLoyalty' => 0.00, // You may need to implement this
            'creditNotes' => 0.00,
            'otherPayments' => 0.00,
        ];
    }

    /**
     * Retry failed invoice
     */
    public function retryFailedInvoice($invoice_log_id)
    {
        $invoice_log = HcmInvoiceLog::find($invoice_log_id);
        
        if (!$invoice_log) {
            throw new \Exception('Invoice log not found');
        }

        $config = HcmTenantConfig::where('business_id', $invoice_log->business_id)
                                ->where('location_id', $invoice_log->location_id)
                                ->first();

        if (!$config) {
            throw new \Exception('Tenant configuration not found');
        }

        $transaction = Transaction::find($invoice_log->transaction_id);
        
        if (!$transaction) {
            throw new \Exception('Transaction not found');
        }

        $this->syncSingleInvoice($transaction, $config, auth()->id());

        return [
            'success' => true,
            'msg' => 'Invoice retry initiated successfully'
        ];
    }

    /**
     * Generate Excel report
     */
    public function generateExcelReport($business_id, $report_type, $location_id, $start_date, $end_date)
    {
        // Implementation for Excel report generation
        // This would use a library like PhpSpreadsheet or Laravel Excel
        throw new \Exception('Excel report generation not implemented yet');
    }

    /**
     * Get last sync time
     */
    public function getLastSync($business_id, $sync_type, $format = true)
    {
        $sync_log = HcmSyncLog::where('business_id', $business_id)
                             ->where('sync_type', $sync_type)
                             ->orderBy('created_at', 'desc')
                             ->first();

        if (!$sync_log) {
            return $format ? 'Never' : null;
        }

        return $format ? $sync_log->created_at->format('Y-m-d H:i:s') : $sync_log->created_at;
    }

    /**
     * Create sync log
     */
    public function createSyncLog($business_id, $user_id, $sync_type, $operation_type, $data = null, $details = null)
    {
        HcmSyncLog::create([
            'business_id' => $business_id,
            'sync_type' => $sync_type,
            'operation_type' => $operation_type,
            'data' => $data ? json_encode($data) : null,
            'details' => $details ? json_encode($details) : null,
            'created_by' => $user_id,
        ]);
    }

    /**
     * Send ping to HCM
     */
    public function sendPing($business_id, $location_id)
    {
        $config = HcmTenantConfig::where('business_id', $business_id)
                                ->where('location_id', $location_id)
                                ->first();

        if (!$config) {
            return;
        }

        try {
            $client = new Client();
            $response = $client->post($config->api_url . '/api/ping', [
                'json' => [
                    'tenantId' => $config->tenant_id,
                    'posId' => $config->pos_id,
                    'timestamp' => now()->toISOString()
                ],
                'timeout' => 10
            ]);

            $status = $response->getStatusCode() == 200 ? 'online' : 'offline';

            HcmPingLog::updateOrCreate([
                'business_id' => $business_id,
                'location_id' => $location_id,
            ], [
                'status' => $status,
                'response_data' => $response->getBody(),
                'response_message' => 'Success',
                'last_ping_at' => now(),
            ]);

        } catch (\Exception $e) {
            HcmPingLog::updateOrCreate([
                'business_id' => $business_id,
                'location_id' => $location_id,
            ], [
                'status' => 'offline',
                'response_message' => $e->getMessage(),
                'last_ping_at' => now(),
            ]);
        }
    }
}
