<?php

namespace Modules\Gallface\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Modules\Gallface\Models\LocationApiCredential;

class SaleCreatedListener
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        try {
            // Get the transaction from the event
            $transaction = $event->transaction ?? null;

            // Handle both sales and returns
            if (!$transaction || !in_array($transaction->type, ['sell', 'sell_return'])) {
                return;
            }

            $locationId = $transaction->location_id;
            $businessId = $transaction->business_id;

            $transactionType = $transaction->type === 'sell_return' ? 'Return' : 'Sale';
            Log::info($transactionType . ' Created - Starting Gallface & HCM Sync', [
                'transaction_id' => $transaction->id,
                'invoice_no' => $transaction->invoice_no,
                'transaction_type' => $transaction->type,
                'location_id' => $locationId,
                'business_id' => $businessId
            ]);

            // Check for Gallface auto-sync
            $gallfaceCredential = LocationApiCredential::where('business_id', $businessId)
                ->where('business_location_id', $locationId)
                ->where('mall_code', 'gallface')
                ->where('is_active', true)
                ->where('auto_sync_enabled', true)
                ->first();

            if ($gallfaceCredential) {
                Log::info('Triggering Gallface auto-sync for new ' . $transactionType, [
                    'location_id' => $locationId,
                    'invoice_no' => $transaction->invoice_no,
                    'transaction_type' => $transaction->type
                ]);

                // Run sync in background
                $gallfaceResult = Artisan::call('gallface:sync', [
                    '--location-id' => $locationId,
                    '--auto' => true,
                    '--transaction-id' => $transaction->id, // Pass transaction ID for targeted sync
                    '--transaction-type' => $transaction->type, // Pass transaction type
                ]);

                // Log sync result
                if ($gallfaceResult === 0) { // Artisan::call returns 0 on success
                    Log::info('Gallface Sync Successful (' . $transactionType . ')', [
                        'transaction_id' => $transaction->id,
                        'invoice_no' => $transaction->invoice_no,
                        'transaction_type' => $transaction->type,
                        'records_synced' => 'N/A' // Detailed count might not be available directly from Artisan::call return
                    ]);
                } else {
                    Log::error('Gallface Sync Failed (' . $transactionType . ')', [
                        'transaction_id' => $transaction->id,
                        'invoice_no' => $transaction->invoice_no,
                        'transaction_type' => $transaction->type,
                        'exit_code' => $gallfaceResult
                    ]);
                }
            }

            // Check for HCM auto-sync
            $hcmCredential = LocationApiCredential::where('business_id', $businessId)
                ->where('business_location_id', $locationId)
                ->where('mall_code', 'hcm')
                ->where('is_active', true)
                ->where('auto_sync_enabled', true)
                ->first();

            if ($hcmCredential) {
                Log::info('Triggering HCM auto-sync for new ' . $transactionType, [
                    'location_id' => $locationId,
                    'invoice_no' => $transaction->invoice_no,
                    'transaction_type' => $transaction->type
                ]);

                // Run sync in background
                $hcmResult = Artisan::call('hcm:sync', [
                    '--location-id' => $locationId,
                    '--auto' => true,
                    '--transaction-id' => $transaction->id, // Pass transaction ID for targeted sync
                    '--transaction-type' => $transaction->type, // Pass transaction type
                ]);

                // Log sync result
                if ($hcmResult === 0) { // Artisan::call returns 0 on success
                    Log::info('HCM Sync Successful (' . $transactionType . ')', [
                        'transaction_id' => $transaction->id,
                        'invoice_no' => $transaction->invoice_no,
                        'transaction_type' => $transaction->type,
                        'records_synced' => 'N/A' // Detailed count might not be available directly from Artisan::call return
                    ]);
                } else {
                    Log::error('HCM Sync Failed (' . $transactionType . ')', [
                        'transaction_id' => $transaction->id,
                        'invoice_no' => $transaction->invoice_no,
                        'transaction_type' => $transaction->type,
                        'exit_code' => $hcmResult
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Sale Created Listener Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'transaction_id' => isset($transaction->id) ? $transaction->id : 'N/A',
                'invoice_no' => isset($transaction->invoice_no) ? $transaction->invoice_no : 'N/A',
                'transaction_type' => isset($transaction->type) ? $transaction->type : 'N/A',
            ]);
        }
    }
}