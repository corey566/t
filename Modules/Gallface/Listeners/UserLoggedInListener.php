<?php

namespace Modules\Gallface\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\Gallface\Models\LocationApiCredential;
use Modules\Gallface\Services\HcmApiService;

class UserLoggedInListener
{
    public function handle($event)
    {
        try {
            // Get the user from the event
            $user = $event->user ?? auth()->user();
            
            if (!$user || !isset($user->location_id)) {
                Log::info('HCM Ping Skipped - No user or location_id', [
                    'has_user' => isset($user),
                    'has_location' => isset($user->location_id ?? null)
                ]);
                return;
            }

            $locationId = $user->location_id;
            $businessId = $user->business_id;
            $userId = $user->id;
            $username = $user->username ?? $user->first_name ?? 'Unknown';
            $ipAddress = request()->ip();

            Log::info('User Login Detected', [
                'user_id' => $userId,
                'username' => $username,
                'location_id' => $locationId,
                'business_id' => $businessId,
                'ip_address' => $ipAddress
            ]);

            // Check for HCM credentials at this location
            $hcmCredential = LocationApiCredential::where('business_id', $businessId)
                ->where('business_location_id', $locationId)
                ->where('mall_code', 'hcm')
                ->where('is_active', true)
                ->first();

            if ($hcmCredential) {
                Log::info('HCM Credentials Found - Sending ping', [
                    'location_id' => $locationId,
                    'user_id' => $userId,
                    'username' => $username,
                    'tenant_id' => $hcmCredential->username,
                    'pos_id' => $hcmCredential->pos_id
                ]);
                
                // Send immediate ping
                $apiService = new HcmApiService($hcmCredential->getCredentialsForApi());
                $pingResult = $apiService->sendPing($userId, $username, $ipAddress);
                
                // Log the ping to database
                DB::table('hcm_ping_logs')->insert([
                    'location_id' => $locationId,
                    'user_id' => $userId,
                    'username' => $username,
                    'ip_address' => $ipAddress,
                    'tenant_id' => $hcmCredential->username,
                    'pos_id' => $hcmCredential->pos_id,
                    'success' => $pingResult['success'],
                    'message' => $pingResult['message'],
                    'response_data' => isset($pingResult['response']) ? json_encode($pingResult['response']) : null,
                    'pinged_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                Log::info('HCM Login Ping Completed', [
                    'success' => $pingResult['success'],
                    'message' => $pingResult['message']
                ]);
            } else {
                Log::info('No HCM credentials found for location', [
                    'location_id' => $locationId,
                    'business_id' => $businessId
                ]);
            }

        } catch (\Exception $e) {
            Log::error('User Logged In Listener Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
