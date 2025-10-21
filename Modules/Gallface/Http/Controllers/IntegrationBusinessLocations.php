<?php

namespace Modules\Gallface\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\BusinessLocation;
use Modules\Gallface\Models\LocationApiCredential;

class IntegrationBusinessLocations extends Controller
{
    /**
     * Get all business locations with integration credentials
     */
    public function index(Request $request)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $mallCode = $request->input('mall_code', 'gallface');

            $locations = BusinessLocation::where('business_id', $business_id)
                ->where('is_active', true)
                ->with(['credentials' => function($query) use ($mallCode) {
                    $query->where('mall_code', $mallCode);
                }])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch locations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific business location with credentials
     */
    public function show($id, Request $request)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $mallCode = $request->input('mall_code', 'gallface');

            $location = BusinessLocation::where('id', $id)
                ->where('business_id', $business_id)
                ->with(['credentials' => function($query) use ($mallCode) {
                    $query->where('mall_code', $mallCode);
                }])
                ->first();

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $location
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch location: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get credentials for a specific location and mall
     */
    public function getCredentials($location_id, Request $request)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $mallCode = $request->input('mall_code', 'gallface');

            $credential = LocationApiCredential::where('business_id', $business_id)
                ->where('business_location_id', $location_id)
                ->where('mall_code', $mallCode)
                ->first();

            if (!$credential) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credentials not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $credential
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch credentials: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get locations with active integrations
     */
    public function getActiveIntegrations(Request $request)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $mallCode = $request->input('mall_code');

            $query = LocationApiCredential::where('business_id', $business_id)
                ->where('is_active', true);

            if ($mallCode) {
                $query->where('mall_code', $mallCode);
            }

            $credentials = $query->with('location')->get();

            return response()->json([
                'success' => true,
                'data' => $credentials
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active integrations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle location integration status
     */
    public function toggleStatus($credential_id, Request $request)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            $credential = LocationApiCredential::where('business_id', $business_id)
                ->where('id', $credential_id)
                ->firstOrFail();

            $credential->is_active = !$credential->is_active;
            $credential->save();

            return response()->json([
                'success' => true,
                'message' => 'Integration status updated successfully',
                'is_active' => $credential->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }
}
