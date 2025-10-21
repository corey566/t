<?php

namespace Modules\Gallface\Console;

use Illuminate\Console\Command;
use Modules\Gallface\Models\LocationApiCredential;
use Modules\Gallface\Services\HcmApiService;
use Illuminate\Support\Facades\Log;

class HcmPingCommand extends Command
{
    protected $signature = 'hcm:ping {--once : Run ping once and exit}';
    protected $description = 'Automatically send ping to HCM API to monitor POS terminal live status';

    public function handle()
    {
        $runOnce = $this->option('once');
        
        $this->info('HCM Auto-Ping Service Started');
        
        do {
            $this->sendPingsToAllLocations();
            
            if (!$runOnce) {
                // Wait for 1 minute before next check (actual ping interval is per location)
                sleep(60);
            }
        } while (!$runOnce);
        
        return 0;
    }

    protected function sendPingsToAllLocations()
    {
        $credentials = LocationApiCredential::where('mall_code', 'hcm')
            ->where('is_active', true)
            ->get();
        
        if ($credentials->isEmpty()) {
            $this->info('No active HCM credentials found.');
            return;
        }
        
        foreach ($credentials as $credential) {
            try {
                // Check if ping is due based on ping_interval
                $pingInterval = $credential->ping_interval ?? 5; // Default 5 minutes
                $lastPing = $credential->last_ping_at;
                
                if ($lastPing && $lastPing->addMinutes($pingInterval)->isFuture()) {
                    // Not yet time to ping this location
                    continue;
                }
                
                $locationName = $credential->businessLocation->name ?? 'Unknown';
                $this->info("Pinging location: {$locationName} (Interval: {$pingInterval} min)");
                
                $apiService = new HcmApiService($credential->getCredentialsForApi());
                $pingResult = $apiService->sendPing();
                
                if ($pingResult['success']) {
                    $this->info("✓ Ping successful for {$locationName}");
                    
                    // Update last ping timestamp
                    $credential->update(['last_ping_at' => now()]);
                } else {
                    $this->error("✗ Ping failed for {$locationName}: {$pingResult['message']}");
                    
                    Log::error('HCM Ping Failed', [
                        'location_id' => $credential->business_location_id,
                        'location_name' => $locationName,
                        'error' => $pingResult['message']
                    ]);
                }
            } catch (\Exception $e) {
                $this->error("Exception pinging location {$credential->business_location_id}: {$e->getMessage()}");
                
                Log::error('HCM Ping Exception', [
                    'location_id' => $credential->business_location_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
