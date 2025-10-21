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
            
            // Check if this location has HCM integration enabled with auto-sync
            $credential = LocationApiCredential::where('business_id', $transaction->business_id)
                ->where('business_location_id', $transaction->location_id)
                ->where('mall_code', 'hcm')
                ->where('is_active', true)
                ->where('auto_sync_enabled', true)
                ->first();
            
            if (!$credential) {
                return;
            }
            
            // Check if already synced
            if (!empty($transaction->hcm_synced_at)) {
                return;
            }
            
            $apiService = new HcmApiService([
                'api_url' => $credential->api_url,
                'username' => $credential->username,
                'password' => $credential->password,
                'stall_no' => $credential->stall_no,
                'pos_id' => $credential->pos_id,
            ]);
            
            $result = $apiService->syncSales(collect([$transaction]), $transaction->location_id);
            
            if ($result['success']) {
                DB::table('transactions')
                    ->where('id', $transaction->id)
                    ->update(['hcm_synced_at' => now()]);
                
                Log::info('HCM: Auto-synced sale', [
                    'invoice_no' => $transaction->invoice_no,
                    'location_id' => $transaction->location_id
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('HCM Auto-Sync Failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id ?? null
            ]);
        }
    }
}
