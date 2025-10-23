<?php

namespace Modules\Gallface\Listeners;

use App\Events\SellCreatedOrModified;
use Modules\Gallface\Models\LocationApiCredential;
use Modules\Gallface\Services\GallfaceApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GallfaceSaleCreatedListener
{
    public function handle(SellCreatedOrModified $event)
    {
        try {
            $transaction = $event->transaction;
            
            Log::info('Gallface: Sale event received', [
                'transaction_id' => $transaction->id,
                'invoice_no' => $transaction->invoice_no,
                'type' => $transaction->type
            ]);
            
            // Only sync sell transactions
            if ($transaction->type !== 'sell') {
                Log::info('Gallface: Skipping non-sell transaction', [
                    'type' => $transaction->type
                ]);
                return;
            }
            
            // Check if this location has Gallface integration enabled with auto-sync
            $credential = LocationApiCredential::where('business_id', $transaction->business_id)
                ->where('business_location_id', $transaction->location_id)
                ->where('mall_code', 'gallface')
                ->where('is_active', true)
                ->where('auto_sync_enabled', true)
                ->first();
            
            if (!$credential) {
                Log::info('Gallface: No auto-sync credentials found', [
                    'business_id' => $transaction->business_id,
                    'location_id' => $transaction->location_id
                ]);
                return;
            }
            
            // Check if already synced
            if (!empty($transaction->gallface_synced_at)) {
                Log::info('Gallface: Transaction already synced', [
                    'invoice_no' => $transaction->invoice_no,
                    'synced_at' => $transaction->gallface_synced_at
                ]);
                return;
            }
            
            Log::info('Gallface: Starting auto-sync', [
                'invoice_no' => $transaction->invoice_no
            ]);
            
            $additionalData = json_decode($credential->additional_data ?? '{}', true);
            
            $apiService = new GallfaceApiService([
                'access_token_url' => $credential->api_url,
                'production_url' => $additionalData['production_url'] ?? '',
                'client_id' => $credential->client_id,
                'client_secret' => $credential->client_secret,
                'property_code' => $additionalData['property_code'] ?? 'CCB1',
                'pos_interface_code' => $additionalData['pos_interface_code'] ?? $credential->client_id,
                'app_code' => $additionalData['app_code'] ?? 'POS-02'
            ]);
            
            $result = $apiService->syncSales(collect([$transaction]), $transaction->location_id);
            
            if ($result['success']) {
                DB::table('transactions')
                    ->where('id', $transaction->id)
                    ->update(['gallface_synced_at' => now()]);
                
                Log::info('Gallface: Auto-synced sale', [
                    'invoice_no' => $transaction->invoice_no,
                    'location_id' => $transaction->location_id
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Gallface Auto-Sync Failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id ?? null
            ]);
        }
    }
}
