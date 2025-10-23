
<?php

namespace Modules\Gallface\Listeners;

use App\Events\SellCreatedOrModified;
use Modules\Gallface\Models\LocationApiCredential;
use Modules\Gallface\Services\HcmApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HcmSaleCreatedListener
{
    public function handle(SellCreatedOrModified $event)
    {
        try {
            $transaction = $event->transaction;
            
            Log::info('HCM: Sale event received', [
                'transaction_id' => $transaction->id,
                'invoice_no' => $transaction->invoice_no,
                'type' => $transaction->type
            ]);
            
            // Only sync sell transactions (not quotations, drafts, etc)
            if ($transaction->type !== 'sell') {
                Log::info('HCM: Skipping non-sell transaction', [
                    'type' => $transaction->type
                ]);
                return;
            }
            
            // Check if this location has HCM integration enabled with auto-sync
            $credential = LocationApiCredential::where('business_id', $transaction->business_id)
                ->where('business_location_id', $transaction->location_id)
                ->where('mall_code', 'hcm')
                ->where('is_active', true)
                ->where('auto_sync_enabled', true)
                ->first();
            
            if (!$credential) {
                Log::info('HCM: No auto-sync credentials found', [
                    'business_id' => $transaction->business_id,
                    'location_id' => $transaction->location_id
                ]);
                return;
            }
            
            // Check if already synced
            if (!empty($transaction->hcm_synced_at)) {
                Log::info('HCM: Transaction already synced', [
                    'invoice_no' => $transaction->invoice_no,
                    'synced_at' => $transaction->hcm_synced_at
                ]);
                return;
            }
            
            Log::info('HCM: Starting auto-sync', [
                'invoice_no' => $transaction->invoice_no,
                'tenant_id' => $credential->username
            ]);
            
            $apiService = new HcmApiService([
                'api_url' => $credential->api_url,
                'username' => $credential->username,
                'password' => $credential->password,
                'stall_no' => $credential->stall_no,
                'pos_id' => $credential->pos_id,
            ]);
            
            // Load full transaction with relationships
            $fullTransaction = \DB::table('transactions')
                ->where('id', $transaction->id)
                ->first();
            
            $result = $apiService->syncSales([$fullTransaction], $transaction->location_id);
            
            if ($result['success']) {
                \DB::table('transactions')
                    ->where('id', $transaction->id)
                    ->update(['hcm_synced_at' => now()]);
                
                Log::info('HCM: ✓ Auto-synced sale successfully', [
                    'invoice_no' => $transaction->invoice_no,
                    'location_id' => $transaction->location_id,
                    'records_synced' => $result['records_synced']
                ]);
            } else {
                Log::error('HCM: ✗ Auto-sync failed', [
                    'invoice_no' => $transaction->invoice_no,
                    'error' => $result['message']
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('HCM Auto-Sync Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'transaction_id' => isset($transaction) ? $transaction->id : null
            ]);
        }
    }
}
