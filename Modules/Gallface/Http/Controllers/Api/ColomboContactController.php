<?php

namespace Modules\Gallface\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColomboContactController extends ColomboApiController
{
    /**
     * Get all contacts
     */
    public function index(Request $request, $locationId)
    {
        try {
            $location = $this->validateLocation($locationId);
            if ($location instanceof \Illuminate\Http\JsonResponse) {
                return $location;
            }

            $contacts = DB::table('contacts')
                ->where('business_id', $this->businessId)
                ->where('type', 'customer')
                ->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 50));

            return response()->json([
                'success' => true,
                'data' => $contacts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch contacts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific contact
     */
    public function show($locationId, $id)
    {
        try {
            $location = $this->validateLocation($locationId);
            if ($location instanceof \Illuminate\Http\JsonResponse) {
                return $location;
            }

            $contact = DB::table('contacts')
                ->where('id', $id)
                ->where('business_id', $this->businessId)
                ->first();

            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $contact
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch contact: ' . $e->getMessage()
            ], 500);
        }
    }
}
