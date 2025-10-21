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
            // Get the user from the login event
            $user = $event->user ?? auth()->user();
            
            if (!$user || !isset($user->location_id)) {
                Log::warning('HCM Login Listener - No user or location', [
                    'has_user' => !empty($user),
                    'has_location' => isset($user->location_id)
                ]);
                return;
            }
            
            Log::info('HCM Login Listener Triggered', [
                'user_id' => $user->id,
                'location_id' => $user->location_id,
                'business_id' => $user->business_id
            ]);

            $locationId = $user->location_id;
            $businessId = $user->business_id;
            $userId = $user->id;
            $username = $user->username ?? $user->first_name ?? 'Unknown';
            $ipAddress = request()->ip();

            // Check for HCM credentials at this location
            $hcmCredential = LocationApiCredential::where('business_id', $businessId)
                ->where('business_location_id', $locationId)
                ->where('mall_code', 'hcm')
                ->where('is_active', true)
                ->first();

            if ($hcmCredential) {
                Log::info('User logged in - Sending HCM ping', [
                    'location_id' => $locationId,
                    'user_id' => $userId,
                    'username' => $username,
                    'ip_address' => $ipAddress
                ]);
                
                // Send immediate ping on login
                $apiService = new HcmApiService($hcmCredential->getCredentialsForApi());
                $pingResult = $apiService->sendPing($userId, $username, $ipAddress);
                
                // Log the ping
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
                
                Log::info('HCM Login Ping Result', [
                    'success' => $pingResult['success'],
                    'message' => $pingResult['message']
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
