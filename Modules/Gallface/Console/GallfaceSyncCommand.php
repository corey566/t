<?php

namespace Modules\Gallface\Console;

use Illuminate\Console\Command;
use Modules\Gallface\Models\LocationApiCredential;
use Modules\Gallface\Services\GallfaceApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GallfaceSyncCommand extends Command
{
    protected $signature = 'gallface:sync {--location-id= : Sync specific location only} {--auto : Run in auto-sync mode} {--continuous : Run continuously every 5 seconds}';
    protected $description = 'Automatically sync sales data to Gallface MIMS API';

    public function handle()
    {
        $locationId = $this->option('location-id');
        $autoMode = $this->option('auto');
        $continuous = $this->option('continuous');
        
        do {
            $query = LocationApiCredential::where('mall_code', 'gallface')
                ->where('is_active', true);
            
            // If auto mode is enabled, only sync locations with auto_sync_enabled = true
            if ($autoMode) {
                $query->where('auto_sync_enabled', true);
            }
            
            if ($locationId) {
                $query->where('business_location_id', $locationId);
            }
            
            $credentials = $query->get();
            
            if ($credentials->isEmpty()) {
                $this->info('No active Gallface credentials found.');
                if (!$continuous) {
                    return 0;
                }
                sleep(5);
                continue;
            }
            
            foreach ($credentials as $credential) {
            // Safely get location name
            $locationName = $credential->businessLocation ? $credential->businessLocation->name : "Location ID: {$credential->business_location_id}";
            $this->info("Processing location: {$locationName}");
            
            try {
                $additionalData = json_decode($credential->additional_data ?? '{}', true);
                
                // Get unsynced sales and returns for this specific location
                $salesData = DB::table('transactions')
                    ->where('business_id', $credential->business_id)
                    ->where('location_id', $credential->business_location_id)
                    ->whereIn('type', ['sell', 'sell_return'])
                    ->whereNull('gallface_synced_at')
                    ->limit(100)
                    ->get();
                
                if ($salesData->isEmpty()) {
                    $this->info("No new sales or returns to sync for this location");
                    continue;
                }
                
                $apiService = new GallfaceApiService([
                    'access_token_url' => $credential->api_url,
                    'production_url' => $additionalData['production_url'] ?? '',
                    'client_id' => $credential->client_id,
                    'client_secret' => $credential->client_secret,
                    'property_code' => $additionalData['property_code'] ?? 'CCB1',
                    'pos_interface_code' => $additionalData['pos_interface_code'] ?? $credential->client_id,
                    'app_code' => $additionalData['app_code'] ?? 'POS-02'
                ]);
                
                $syncResult = $apiService->syncSales($salesData, $credential->business_location_id);
                
                if ($syncResult['success']) {
                    $this->info("✓ Synced {$syncResult['records_synced']} sales records");
                    
                    // Mark as synced
                    $invoiceNos = $salesData->pluck('invoice_no')->toArray();
                    DB::table('transactions')
                        ->where('business_id', $credential->business_id)
                        ->where('location_id', $credential->business_location_id)
                        ->whereIn('invoice_no', $invoiceNos)
                        ->update(['gallface_synced_at' => now()]);
                    
                    // Update last synced timestamp
                    $credential->update(['last_synced_at' => now()]);
                } else {
                    $this->error("✗ Sync failed: {$syncResult['message']}");
                    Log::error('Gallface Auto-Sync Failed', [
                        'location_id' => $credential->business_location_id,
                        'error' => $syncResult['message']
                    ]);
                }
                
            } catch (\Exception $e) {
                $this->error("✗ Error: {$e->getMessage()}");
                Log::error('Gallface Auto-Sync Exception', [
                    'location_id' => $credential->business_location_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
            
            $this->info('Gallface sync completed!');
            
            if ($continuous) {
                sleep(5); // Wait 5 seconds before next sync
            }
        } while ($continuous);
        
        return 0;
    }
}
