<?php

namespace Modules\Gallface\Console;

use Illuminate\Console\Command;
use Modules\Gallface\Models\LocationApiCredential;
use Modules\Gallface\Services\HcmApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HcmActivityPingCommand extends Command
{
    protected $signature = 'hcm:activity-ping {--location-id= : Monitor specific location only} {--continuous : Run continuously}';
    protected $description = 'Ping HCM API every 5 seconds when users are active (monitors activity_log)';

    public function handle()
    {
        $locationId = $this->option('location-id');
        $continuous = $this->option('continuous');

        $this->info('Starting HCM Activity-Based Ping Monitor (5-second intervals)...');

        do {
            $startTime = microtime(true);

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
                $this->sleep(5, $startTime);
                continue;
            }

            foreach ($credentials as $credential) {
                $locationName = $credential->businessLocation ? $credential->businessLocation->name : "Location ID: {$credential->business_location_id}";

                // Get active user from activity_log table (last 30 seconds)
                $activeUser = $this->getActiveUserFromActivityLog($credential->business_id, $credential->business_location_id);

                if (!$activeUser) {
                    $this->line("⊘ {$locationName} - No recent user activity (skipping ping)");
                    continue;
                }

                $this->info("→ {$locationName} - User: {$activeUser['username']} (IP: {$activeUser['ip_address']}) - Sending ping...");

                $apiService = new HcmApiService($credential->getCredentialsForApi());
                $pingResult = $apiService->sendPing($activeUser['user_id'], $activeUser['username'], $activeUser['ip_address']);

                // Log ping to database
                try {
                    DB::table('hcm_ping_logs')->insert([
                        'location_id' => $credential->business_location_id,
                        'user_id' => $activeUser['user_id'],
                        'username' => $activeUser['username'],
                        'ip_address' => $activeUser['ip_address'],
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
                // Sleep for exactly 5 seconds minus processing time
                $this->sleep(5, $startTime);
            }
        } while ($continuous);

        return 0;
    }

    /**
     * Get active user from activity_log table
     */
    protected function getActiveUserFromActivityLog($businessId, $locationId)
    {
        // Check activity_log for recent activity (last 30 seconds for real-time pinging)
        $activityData = DB::table('activity_log')
            ->join('users', 'activity_log.causer_id', '=', 'users.id')
            ->leftJoin('sessions', 'users.id', '=', 'sessions.user_id')
            ->where('activity_log.business_id', $businessId)
            ->where('users.location_id', $locationId)
            ->where('activity_log.created_at', '>=', now()->subSeconds(30))
            ->select(
                'users.id as user_id',
                'users.username',
                'users.first_name',
                'sessions.ip_address',
                'activity_log.created_at as last_activity'
            )
            ->orderBy('activity_log.created_at', 'desc')
            ->first();

        if ($activityData) {
            return [
                'user_id' => $activityData->user_id,
                'username' => $activityData->username ?? $activityData->first_name ?? 'Unknown',
                'ip_address' => $activityData->ip_address ?? request()->ip() ?? '127.0.0.1',
                'last_activity' => $activityData->last_activity
            ];
        }

        return null;
    }

    /**
     * Sleep for exactly the specified seconds minus processing time
     */
    protected function sleep($seconds, $startTime)
    {
        $elapsedTime = microtime(true) - $startTime;
        $sleepTime = max(0, $seconds - $elapsedTime);

        if ($sleepTime > 0) {
            usleep((int)($sleepTime * 1000000)); // Convert to microseconds
        }
    }
}