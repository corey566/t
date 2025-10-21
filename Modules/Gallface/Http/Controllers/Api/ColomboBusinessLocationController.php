<?php

namespace Modules\Gallface\Http\Controllers\Api;

use App\BusinessLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ColomboBusinessLocationController extends ColomboApiController
{
    /**
     * Get all business locations
     */
    public function index(Request $request)
    {
        try {
            $locations = BusinessLocation::where('business_id', $this->businessId)
                ->where('is_active', true)
                ->with(['credentials' => function($query) {
                    $query->where('mall_code', 'colombo');
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
     * Get specific business location
     */
    public function show($id)
    {
        try {
            $location = BusinessLocation::where('id', $id)
                ->where('business_id', $this->businessId)
                ->with(['credentials' => function($query) {
                    $query->where('mall_code', 'colombo');
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
}
