<?php

namespace Modules\Gallface\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Modules\Gallface\Models\LocationApiCredential;

class SaleCreatedListener
{
    public function handle($event)
    {
        try {
            // Get the transaction/sale from the event
            $transaction = $event->transaction ?? null;
            
            if (!$transaction || $transaction->type !== 'sell') {
                return;
            }

            $locationId = $transaction->location_id;
            $businessId = $transaction->business_id;

            // Check for Gallface auto-sync
            $gallfaceCredential = LocationApiCredential::where('business_id', $businessId)
                ->where('business_location_id', $locationId)
                ->where('mall_code', 'gallface')
                ->where('is_active', true)
                ->where('auto_sync_enabled', true)
                ->first();

            if ($gallfaceCredential) {
                Log::info('Triggering Gallface auto-sync for new sale', [
                    'location_id' => $locationId,
                    'invoice_no' => $transaction->invoice_no
                ]);
                
                // Run sync in background
                Artisan::call('gallface:sync', [
                    '--location-id' => $locationId,
                    '--auto' => true
                ]);
            }

            // Check for HCM auto-sync
            $hcmCredential = LocationApiCredential::where('business_id', $businessId)
                ->where('business_location_id', $locationId)
                ->where('mall_code', 'hcm')
                ->where('is_active', true)
                ->where('auto_sync_enabled', true)
                ->first();

            if ($hcmCredential) {
                Log::info('Triggering HCM auto-sync for new sale', [
                    'location_id' => $locationId,
                    'invoice_no' => $transaction->invoice_no
                ]);
                
                // Run sync in background
                Artisan::call('hcm:sync', [
                    '--location-id' => $locationId,
                    '--auto' => true
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Sale Created Listener Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
