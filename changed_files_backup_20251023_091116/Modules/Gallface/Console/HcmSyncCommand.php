<?php
namespace Modules\Gallface\Console;

use Illuminate\Console\Command;
use Modules\Gallface\Models\LocationApiCredential;
use Modules\Gallface\Services\HcmApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HcmSyncCommand extends Command
{
    protected $signature = 'hcm:sync {--ping-only : Only send ping without syncing sales} {--location-id= : Sync specific location only} {--auto : Run in auto-sync mode} {--continuous : Run continuously every 5 seconds}';
    protected $description = 'Sync sales data to HCM API and send ping to monitor POS status';

    public function handle()
    {
        $pingOnly = $this->option('ping-only');
        $locationId = $this->option('location-id');
        $autoMode = $this->option('auto');
        $continuous = $this->option('continuous');

        do {
            $query = LocationApiCredential::where('mall_code', 'hcm')
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
                $this->info('No active HCM credentials found.');
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

            // Check for active user for the location
            $activeUser = DB::table('activity_log')
                ->where('location_id', $credential->business_location_id)
                ->where('logged_in', true)
                ->orderBy('last_activity_at', 'desc')
                ->first();

            // If no active user, skip ping and sync for this location
            if (!$activeUser) {
                $this->info("No active user found for location: {$locationName}. Skipping ping and sync.");
                continue;
            }

            $apiService = new HcmApiService($credential->getCredentialsForApi());

            // Send ping
            $pingResult = $apiService->sendPing();
            $this->info("Ping result: " . ($pingResult['success'] ? '✓ Success' : '✗ Failed') . " - {$pingResult['message']}");

            // Log ping to database
            try {
                DB::table('hcm_ping_logs')->insert([
                    'location_id' => $credential->business_location_id,
                    'user_id' => $activeUser->user_id,
                    'username' => $activeUser->username, // Assuming username is available in activity_log
                    'ip_address' => '127.0.0.1', // System ping
                    'tenant_id' => $credential->username,
                    'pos_id' => $credential->pos_id,
                    'success' => $pingResult['success'],
                    'message' => $pingResult['message'],
                    'response_data' => isset($pingResult['response']) ? json_encode($pingResult['response']) : null,
                    'pinged_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to log ping: " . $e->getMessage());
            }

            if ($pingOnly) {
                continue;
            }

                // Sync sales data
                $salesData = DB::table('transactions')
                    ->where('business_id', $credential->business_id)
                    ->where('location_id', $credential->business_location_id)
                    ->where('type', 'sell')
                    ->whereNull('hcm_synced_at')
                    ->limit(100)
                    ->get()
                    ->toArray();

                if (!empty($salesData)) {
                    $syncResult = $apiService->syncSales($salesData, $credential->business_location_id);

                    if ($syncResult['success']) {
                        $this->info("✓ Synced {$syncResult['records_synced']} sales records");

                        // Mark as synced
                        $invoiceNos = array_column($salesData, 'invoice_no');
                        DB::table('transactions')
                            ->whereIn('invoice_no', $invoiceNos)
                            ->update(['hcm_synced_at' => now()]);
                    } else {
                        $this->error("✗ Sync failed: {$syncResult['message']}");
                    }
                } else {
                    $this->info("No new sales to sync");
                }

            // Update last synced timestamp
            $credential->update(['last_synced_at' => now()]);
        }

            $this->info('HCM sync completed successfully!');

            if ($continuous) {
                sleep(5); // Wait 5 seconds before next sync
            }
        } while ($continuous);

        return 0;
    }
}