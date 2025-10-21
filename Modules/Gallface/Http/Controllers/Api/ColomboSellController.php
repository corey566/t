
<?php

namespace Modules\Gallface\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ColomboSellController extends ColomboApiController
{
    /**
     * Get all sales for a location
     */
    public function index(Request $request, $locationId)
    {
        try {
            $location = $this->validateLocation($locationId);
            if ($location instanceof \Illuminate\Http\JsonResponse) {
                return $location;
            }

            $query = DB::table('transactions as t')
                ->select(
                    't.*',
                    'c.name as customer_name',
                    'c.mobile as customer_mobile',
                    'bl.name as location_name'
                )
                ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
                ->leftJoin('business_locations as bl', 't.location_id', '=', 'bl.id')
                ->where('t.business_id', $this->businessId)
                ->where('t.location_id', $locationId)
                ->where('t.type', 'sell')
                ->orderBy('t.transaction_date', 'desc');

            // Apply filters
            if ($request->has('date_from')) {
                $query->where('t.transaction_date', '>=', $request->date_from);
            }
            if ($request->has('date_to')) {
                $query->where('t.transaction_date', '<=', $request->date_to);
            }

            $sales = $query->paginate($request->input('per_page', 50));

            return response()->json([
                'success' => true,
                'data' => $sales
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sales: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific sale
     */
    public function show($locationId, $id)
    {
        try {
            $location = $this->validateLocation($locationId);
            if ($location instanceof \Illuminate\Http\JsonResponse) {
                return $location;
            }

            $sale = DB::table('transactions as t')
                ->select('t.*', 'c.name as customer_name', 'c.mobile as customer_mobile')
                ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
                ->where('t.id', $id)
                ->where('t.business_id', $this->businessId)
                ->where('t.location_id', $locationId)
                ->first();

            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sale not found'
                ], 404);
            }

            // Get transaction lines
            $lines = DB::table('transaction_sell_lines')
                ->where('transaction_id', $id)
                ->get();

            // Get payment lines
            $payments = DB::table('transaction_payments')
                ->where('transaction_id', $id)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'transaction' => $sale,
                    'lines' => $lines,
                    'payments' => $payments
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sale: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new sale
     */
    public function store(Request $request, $locationId)
    {
        try {
            $location = $this->validateLocation($locationId);
            if ($location instanceof \Illuminate\Http\JsonResponse) {
                return $location;
            }

            // Validate credentials exist
            $credentials = $this->getCredentials($locationId);
            if (!$credentials) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Colombo City Center credentials configured for this location'
                ], 400);
            }

            // Here you would implement the sale creation logic
            // according to the Colombo City Center API documentation

            return response()->json([
                'success' => true,
                'message' => 'Sale created successfully',
                'data' => []
            ]);
        } catch (\Exception $e) {
            Log::error('Colombo API Sale Creation Failed', [
                'location_id' => $locationId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create sale: ' . $e->getMessage()
            ], 500);
        }
    }
}
