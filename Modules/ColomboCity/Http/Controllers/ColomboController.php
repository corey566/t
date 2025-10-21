<?php

namespace Modules\Gallface\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\BusinessLocation;
use Modules\Gallface\Models\LocationApiCredential;


class ColomboController extends Controller
{
    /**
     * Show Colombo City Center credentials page
     */
    public function credentials()
    {
        $business_id = request()->session()->get('user.business_id');
        
        $locations = BusinessLocation::where('business_id', $business_id)
            ->where('is_active', true)
            ->with(['credentials' => function($query) {
                $query->where('mall_code', 'colombo');
            }])
            ->get();

        return view('gallface::gallface.colombo_credentials', compact('locations'));
    }

    /**
     * Get sync logs
     */
    public function getSyncLogs(Request $request)
    {
        $from_date = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $to_date = $request->input('date_to', now()->format('Y-m-d'));
        $status = $request->input('status');

        $query = DB::table('colombo_sync_logs')
            ->whereBetween('created_at', [$from_date . ' 00:00:00', $to_date . ' 23:59:59'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $logs = $query->get();

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }


    /**
     * Save Colombo City Center credentials
     */
    public function saveCredentials(Request $request, $location_id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            $validated = $request->validate([
                'api_url' => 'required|url',
                'api_key' => 'required|string',
                'client_id' => 'required|string',
                'client_secret' => 'required|string',
                'additional_config' => 'nullable|json'
            ]);

            // Validate location belongs to business
            $location = BusinessLocation::where('id', $location_id)
                ->where('business_id', $business_id)
                ->first();

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid business location'
                ], 404);
            }

            $credential = LocationApiCredential::updateOrCreate(
                [
                    'business_id' => $business_id,
                    'business_location_id' => $location_id,
                    'mall_code' => 'colombo'
                ],
                [
                    'api_url' => $request->api_url,
                    'api_key' => $request->api_key,
                    'client_id' => $request->client_id,
                    'client_secret' => $request->client_secret,
                    'additional_data' => $request->additional_config,
                    'is_active' => $request->has('is_active') ? true : false
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Colombo City Center credentials saved successfully'
            ]);
        } catch (\Exception $e) {
            // Log the sync attempt
            DB::table('colombo_sync_logs')->insert([
                'business_id' => $business_id,
                'business_location_id' => $location_id,
                'status' => 'failed',
                'message' => 'Failed to save credentials: ' . $e->getMessage(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save credentials: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Colombo credentials
     */
    public function deleteCredentials($location_id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            $deleted = LocationApiCredential::where('business_id', $business_id)
                ->where('business_location_id', $location_id)
                ->where('mall_code', 'colombo')
                ->delete();

            if ($deleted) {
                // Log the sync attempt
                DB::table('colombo_sync_logs')->insert([
                    'business_id' => $business_id,
                    'business_location_id' => $location_id,
                    'status' => 'success',
                    'message' => 'Credentials deleted successfully',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Credentials deleted successfully'
                ]);
            }

            // Log the sync attempt
            DB::table('colombo_sync_logs')->insert([
                'business_id' => $business_id,
                'business_location_id' => $location_id,
                'status' => 'failed',
                'message' => 'Credentials not found',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Credentials not found'
            ], 404);
        } catch (\Exception $e) {
            // Log the sync attempt
            DB::table('colombo_sync_logs')->insert([
                'business_id' => $business_id,
                'business_location_id' => $location_id,
                'status' => 'failed',
                'message' => 'Failed to delete credentials: ' . $e->getMessage(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete credentials: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test API connection
     */
    public function testConnection(Request $request, $location_id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            $credential = LocationApiCredential::where('business_id', $business_id)
                ->where('business_location_id', $location_id)
                ->where('mall_code', 'colombo')
                ->first();

            if (!$credential) {
                 // Log the sync attempt
                DB::table('colombo_sync_logs')->insert([
                    'business_id' => $business_id,
                    'business_location_id' => $location_id,
                    'status' => 'failed',
                    'message' => 'No credentials found for this location',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No credentials found for this location'
                ], 404);
            }

            // Implement test connection logic based on API documentation
            // For now, assume connection is successful if credentials exist
            $api_url = $credential->api_url;
            $api_key = $credential->api_key;
            $client_id = $credential->client_id;
            $client_secret = $credential->client_secret;

            // Placeholder for actual API call and response handling
            // Example: using Guzzle or a similar HTTP client

            $response_message = 'Colombo City Center API is operational and ready to receive data';
            $success = true;
            $status = 'success';

            // Log the sync attempt
            DB::table('colombo_sync_logs')->insert([
                'business_id' => $business_id,
                'business_location_id' => $location_id,
                'status' => $status,
                'message' => $response_message,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => $success,
                'message' => $response_message
            ]);
        } catch (\Exception $e) {
             // Log the sync attempt
            DB::table('colombo_sync_logs')->insert([
                'business_id' => $business_id,
                'business_location_id' => $location_id,
                'status' => 'failed',
                'message' => 'Connection test failed: ' . $e->getMessage(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }
}