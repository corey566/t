<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Modules\Gallface\Models\LocationApiCredential;

class IntegrationBusinessLocations extends Model
{
    protected $table = 'business_locations';

    /**
     * Get all business locations with their API credentials
     */
    public static function getLocationsWithCredentials($business_id, $mall_code = null)
    {
        $query = BusinessLocation::where('business_id', $business_id)
            ->where('is_active', true)
            ->with(['credentials' => function($q) use ($mall_code) {
                if ($mall_code) {
                    $q->where('mall_code', $mall_code);
                }
                $q->where('is_active', true);
            }]);

        return $query->get();
    }

    /**
     * Get locations that have active integrations
     */
    public static function getActiveIntegrationLocations($business_id, $mall_code = null)
    {
        $query = BusinessLocation::where('business_id', $business_id)
            ->where('is_active', true)
            ->whereHas('credentials', function($q) use ($mall_code) {
                $q->where('is_active', true);
                if ($mall_code) {
                    $q->where('mall_code', $mall_code);
                }
            })
            ->with(['credentials' => function($q) use ($mall_code) {
                $q->where('is_active', true);
                if ($mall_code) {
                    $q->where('mall_code', $mall_code);
                }
            }]);

        return $query->get();
    }

    /**
     * Check if a location has credentials for a specific mall
     */
    public static function hasCredentialsForMall($location_id, $business_id, $mall_code)
    {
        return LocationApiCredential::where('business_location_id', $location_id)
            ->where('business_id', $business_id)
            ->where('mall_code', $mall_code)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get credential details for a location and mall
     */
    public static function getCredentials($location_id, $business_id, $mall_code)
    {
        return LocationApiCredential::where('business_location_id', $location_id)
            ->where('business_id', $business_id)
            ->where('mall_code', $mall_code)
            ->first();
    }

    /**
     * Get all locations without credentials for a specific mall
     */
    public static function getLocationsWithoutCredentials($business_id, $mall_code)
    {
        return BusinessLocation::where('business_id', $business_id)
            ->where('is_active', true)
            ->whereDoesntHave('credentials', function($q) use ($mall_code) {
                $q->where('mall_code', $mall_code);
            })
            ->get();
    }

    /**
     * Toggle integration status for a location
     */
    public static function toggleIntegrationStatus($credential_id, $business_id)
    {
        $credential = LocationApiCredential::where('id', $credential_id)
            ->where('business_id', $business_id)
            ->first();

        if ($credential) {
            $credential->is_active = !$credential->is_active;
            $credential->save();
            return $credential;
        }

        return null;
    }

    /**
     * Get integration summary for business
     */
    public static function getIntegrationSummary($business_id)
    {
        $total_locations = BusinessLocation::where('business_id', $business_id)
            ->where('is_active', true)
            ->count();

        $credentials = LocationApiCredential::where('business_id', $business_id)
            ->where('is_active', true)
            ->get()
            ->groupBy('mall_code');

        $summary = [
            'total_locations' => $total_locations,
            'integrations' => []
        ];

        foreach ($credentials as $mall_code => $creds) {
            $summary['integrations'][$mall_code] = [
                'count' => $creds->count(),
                'locations' => $creds->pluck('business_location_id')->toArray()
            ];
        }

        return $summary;
    }
}
