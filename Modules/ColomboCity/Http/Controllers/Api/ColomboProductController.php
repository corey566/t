<?php

namespace Modules\Gallface\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColomboProductController extends ColomboApiController
{
    /**
     * Get all products for a location
     */
    public function index(Request $request, $locationId)
    {
        try {
            $location = $this->validateLocation($locationId);
            if ($location instanceof \Illuminate\Http\JsonResponse) {
                return $location;
            }

            $products = DB::table('products as p')
                ->select(
                    'p.id',
                    'p.name',
                    'p.sku',
                    'p.type',
                    'p.business_id',
                    'v.id as variation_id',
                    'v.name as variation_name',
                    'v.sub_sku'
                )
                ->leftJoin('variations as v', 'p.id', '=', 'v.product_id')
                ->where('p.business_id', $this->businessId)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $products,
                'location_id' => $locationId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific product
     */
    public function show($locationId, $id)
    {
        try {
            $location = $this->validateLocation($locationId);
            if ($location instanceof \Illuminate\Http\JsonResponse) {
                return $location;
            }

            $product = DB::table('products as p')
                ->select('p.*', 'v.*')
                ->leftJoin('variations as v', 'p.id', '=', 'v.product_id')
                ->where('p.id', $id)
                ->where('p.business_id', $this->businessId)
                ->get();

            if ($product->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product: ' . $e->getMessage()
            ], 500);
        }
    }
}
