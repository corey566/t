<?php

namespace Modules\Gallface\Console;

use Illuminate\Console\Command;
use Modules\Gallface\Models\LocationApiCredential;
use Modules\Gallface\Services\HcmApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HcmActivityPingCommand extends Command
{
    protected $signature = 'hcm:activity-ping {--location-id= : Monitor specific location only} {--continuous : Run continuously}';
    protected $description = 'Ping HCM API only when users are active at a location';

    public function handle()
    {
        $locationId = $this->option('location-id');
        $continuous = $this->option('continuous');
        
        $this->info('Starting HCM Activity-Based Ping Monitor...');
        
        do {
            $query = LocationApiCredential::where('mall_code', 'hcm')
                ->where('is_active', true);
            
            if ($locationId) {
                $query->where('business_location_id', $locationId);
            }
            
            $credentials = $query->get();
            
            if ($credentials->isEmpty()) {
                $this->info('No active HCM credentials found.');
                if (!$continuous) {
                    return 0;
                }
                sleep(5);
                continue;
            }
            
            foreach ($credentials as $credential) {
                $locationName = $credential->businessLocation ? $credential->businessLocation->name : "Location ID: {$credential->business_location_id}";
                
                // Check for active users at this location from activity logs (last 5 minutes)
                $activeUser = DB::table('activity_log')
                    ->join('users', 'activity_log.causer_id', '=', 'users.id')
                    ->where('activity_log.business_id', $credential->business_id)
                    ->where('users.location_id', $credential->business_location_id)
                    ->where('activity_log.created_at', '>=', now()->subMinutes(5))
                    ->select('users.id as user_id', 'users.username', 'users.first_name', 'users.last_ip_address')
                    ->orderBy('activity_log.created_at', 'desc')
                    ->first();
                
                if (!$activeUser) {
                    $this->line("⊘ {$locationName} - No active users (skipping ping)");
                    continue;
                }
                
                $username = $activeUser->username ?? $activeUser->first_name ?? 'Unknown';
                $ipAddress = $activeUser->last_ip_address ?? '127.0.0.1';
                
                $this->info("→ {$locationName} - User: {$username} - Sending ping...");
                
                $apiService = new HcmApiService($credential->getCredentialsForApi());
                $pingResult = $apiService->sendPing($activeUser->user_id, $username, $ipAddress);
                
                // Log ping to database
                try {
                    DB::table('hcm_ping_logs')->insert([
                        'location_id' => $credential->business_location_id,
                        'user_id' => $activeUser->user_id,
                        'username' => $username,
                        'ip_address' => $ipAddress,
                        'tenant_id' => $credential->username,
                        'pos_id' => $credential->pos_id,
                        'success' => $pingResult['success'],
                        'message' => $pingResult['message'],
                        'response_data' => isset($pingResult['response']) ? json_encode($pingResult['response']) : null,
                        'pinged_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    if ($pingResult['success']) {
                        $this->info("  ✓ Ping successful");
                    } else {
                        $this->error("  ✗ Ping failed: {$pingResult['message']}");
                    }
                } catch (\Exception $e) {
                    $this->error("  ✗ Failed to log ping: " . $e->getMessage());
                }
            }
            
            if ($continuous) {
                $this->line('Waiting 30 seconds before next check...');
                sleep(30);
            }
        } while ($continuous);
        
        return 0;
    }
}
