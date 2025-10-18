<?php

namespace Modules\Gallface\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Business;
use Modules\Gallface\Models\LocationApiCredential;

class GallfaceController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('gallface::index');
    }

        public function dashboard()
        {
            $business_id = request()->session()->get('user.business_id');

            /* if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'fardarexpress_module'))) {
                abort(403, 'Unauthorized action.');
            } */


            return view('gallface::gallface.dashboard');
        }

        public function setting(Request $request)
        {
            $business_id = request()->session()->get('user.business_id');

            /* if (! (auth()->user()->can('superadmin') ||
                $this->moduleUtil->hasThePermissionInSubscription($business_id, 'fardarexpress_module'))) {
                abort(403, 'Unauthorized action.');
            } */

            $business = Business::where('id', $business_id)
                                ->firstOrFail();

            $gallfacessetting = json_decode($business->gallface_setting);

            if(!empty($request->input('gallface_access_token_url')) && !empty($request->input('gallface_production_url')) && !empty($request->input('gallface_client_secret')) && !empty($request->input('gallface_client_id'))){

                $gallface_settings = $request->input();	

                $update_date = [
                    'gallface_setting' => $gallface_settings,
                    ];

                $business->fill($update_date);
                $business->update();

            }

            // Get locations and stats for the view
            $locations = \App\BusinessLocation::where('business_id', $business_id)
                ->where('is_active', true)
                ->with(['credentials' => function($query) {
                    $query->where('mall_code', 'gallface');
                }])
                ->get();

            // Debug log to verify credentials are loaded
            foreach ($locations as $location) {
                \Log::info('Location credentials check', [
                    'location_id' => $location->id,
                    'location_name' => $location->name,
                    'credentials_count' => $location->credentials->count(),
                    'credentials' => $location->credentials->toArray()
                ]);
            }

            // Get statistics
            $activeIntegrations = \Modules\Gallface\Models\LocationApiCredential::where('business_id', $business_id)
                ->where('mall_code', 'gallface')
                ->where('is_active', true)
                ->count();

            $recentSyncs = \DB::table('transactions')
                ->where('business_id', $business_id)
                ->whereNotNull('gallface_synced_at')
                ->whereDate('gallface_synced_at', today())
                ->count();

            $lastSync = \Modules\Gallface\Models\LocationApiCredential::where('business_id', $business_id)
                ->where('mall_code', 'gallface')
                ->whereNotNull('last_synced_at')
                ->orderBy('last_synced_at', 'desc')
                ->first();

            $lastSyncTime = $lastSync ? $lastSync->last_synced_at->diffForHumans() : 'Never';

            return view('gallface::gallface.setting', compact(
                'gallfacessetting', 
                'locations', 
                'activeIntegrations', 
                'recentSyncs', 
                'lastSyncTime'
            ));
        }

        /**
         * Show the form for creating a new resource.
         * @return Renderable
         */
        public function create()
        {
            return view('gallface::create');
        }

        /**
         * Store a newly created resource in storage.
         * @param Request $request
         * @return Renderable
         */
        public function store(Request $request)
        {
            //
        }

        /**
         * Show the specified resource.
         * @param int $id
         * @return Renderable
         */
        public function show($id)
        {
            return view('gallface::show');
        }

        /**
         * Show the form for editing the specified resource.
         * @param int $id
         * @return Renderable
         */
        public function edit($id)
        {
            return view('gallface::edit');
        }

        /**
         * Update the specified resource in storage.
         * @param Request $request
         * @param int $id
         * @return Renderable
         */
        public function update(Request $request, $id)
        {
            //
        }

        /**
         * Remove the specified resource from storage.
         * @param int $id
         * @return Renderable
         */
        public function destroy($id)
        {
            //
        }

        /**
         * Save Gallface API connection
         */
        public function saveGallfaceApi(Request $request)
        {
            try {
                $business_id = request()->session()->get('user.business_id');

                $validated = $request->validate([
                    'business_location_id' => 'required|exists:business_locations,id',
                    'access_token_url' => 'required|url',
                    'production_url' => 'required|url',
                    'client_secret' => 'required|string',
                    'client_id' => 'required|string',
                    'property_code' => 'required|string',
                    'pos_interface_code' => 'required|string',
                    'app_code' => 'nullable|string'
                ]);

                // Check if connection already exists for this location
                $existing = \Modules\Gallface\Models\LocationApiCredential::where('business_id', $business_id)
                    ->where('business_location_id', $validated['business_location_id'])
                    ->where('mall_code', 'gallface')
                    ->first();

                if ($existing) {
                    return response()->json([
                        'success' => false,
                        'message' => 'API connection already exists for this location'
                    ], 422);
                }

                $additionalData = [
                    'production_url' => $validated['production_url'],
                    'property_code' => $validated['property_code'],
                    'pos_interface_code' => $validated['pos_interface_code'],
                    'app_code' => $validated['app_code'] ?? 'POS-02'
                ];

                $credential = \Modules\Gallface\Models\LocationApiCredential::create([
                    'business_id' => $business_id,
                    'business_location_id' => $validated['business_location_id'],
                    'mall_code' => 'gallface',
                    'api_url' => $validated['access_token_url'],
                    'client_id' => $validated['client_id'],
                    'client_secret' => $validated['client_secret'],
                    'additional_data' => json_encode($additionalData),
                    'is_active' => true
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'API connection saved successfully'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save API connection: ' . $e->getMessage()
                ], 500);
            }
        }

        /**
         * Update Gallface API connection
         */
        public function updateGallfaceApi(Request $request, $id)
        {
            try {
                $business_id = request()->session()->get('user.business_id');

                $validated = $request->validate([
                    'access_token_url' => 'required|url',
                    'production_url' => 'required|url',
                    'client_secret' => 'required|string',
                    'client_id' => 'required|string',
                    'property_code' => 'required|string',
                    'pos_interface_code' => 'required|string',
                    'app_code' => 'nullable|string',
                    'is_active' => 'nullable|boolean'
                ]);

                $credential = \Modules\Gallface\Models\LocationApiCredential::where('business_id', $business_id)
                    ->where('id', $id)
                    ->where('mall_code', 'gallface')
                    ->firstOrFail();

                // Ensure URLs don't have trailing slashes
                $tokenUrl = rtrim($validated['access_token_url'], '/');
                $productionUrl = rtrim($validated['production_url'], '/');

                $additionalData = [
                    'production_url' => $productionUrl,
                    'property_code' => $validated['property_code'],
                    'pos_interface_code' => $validated['pos_interface_code'],
                    'app_code' => $validated['app_code'] ?? 'POS-02'
                ];

                \Log::info('Gallface: Updating credentials', [
                    'credential_id' => $id,
                    'token_url' => $tokenUrl,
                    'production_url' => $productionUrl,
                    'additional_data' => $additionalData
                ]);

                $credential->update([
                    'api_url' => $tokenUrl,
                    'client_id' => $validated['client_id'],
                    'client_secret' => $validated['client_secret'],
                    'additional_data' => json_encode($additionalData),
                    'is_active' => $request->has('is_active') ? 1 : 0
                ]);

                \Log::info('Gallface credentials updated', [
                    'credential_id' => $id,
                    'location_id' => $credential->business_location_id,
                    'is_active' => $credential->is_active
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'API connection updated successfully'
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to update Gallface credentials: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update API connection: ' . $e->getMessage()
                ], 500);
            }
        }

        /**
         * Test Gallface API connection
         */
        public function testGallfaceConnection($location_id)
        {
            try {
                $business_id = request()->session()->get('user.business_id');

                $credential = \Modules\Gallface\Models\LocationApiCredential::where('business_id', $business_id)
                    ->where('business_location_id', $location_id)
                    ->where('mall_code', 'gallface')
                    ->firstOrFail();

                $additionalData = json_decode($credential->additional_data ?? '{}', true);

                $apiService = new \Modules\Gallface\Services\GallfaceApiService([
                    'access_token_url' => $credential->api_url,
                    'production_url' => $additionalData['production_url'] ?? '',
                    'client_id' => $credential->client_id,
                    'client_secret' => $credential->client_secret,
                    'property_code' => $additionalData['property_code'] ?? 'CCB1',
                    'pos_interface_code' => $additionalData['pos_interface_code'] ?? $credential->client_id
                ]);

                $result = $apiService->testConnection();

                return response()->json($result);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection test failed: ' . $e->getMessage()
                ], 500);
            }
        }

        /**
         * Sync Gallface sales
         */
        public function syncGallfaceSales($location_id)
        {
            try {
                $business_id = request()->session()->get('user.business_id');

                // Verify location belongs to business
                $location = \App\BusinessLocation::where('id', $location_id)
                    ->where('business_id', $business_id)
                    ->firstOrFail();

                $credential = \Modules\Gallface\Models\LocationApiCredential::where('business_id', $business_id)
                    ->where('business_location_id', $location_id)
                    ->where('mall_code', 'gallface')
                    ->firstOrFail();

                // Get unsynced sales for THIS SPECIFIC LOCATION ONLY
                $salesData = \DB::table('transactions')
                    ->where('business_id', $business_id)
                    ->where('location_id', $location_id) // Strict location filter
                    ->where('type', 'sell')
                    ->whereNull('gallface_synced_at')
                    ->limit(100)
                    ->get();

                if ($salesData->isEmpty()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'No new sales to sync for this location',
                        'records_synced' => 0
                    ]);
                }

                $additionalData = json_decode($credential->additional_data ?? '{}', true);

                \Log::info('Gallface: Preparing to sync', [
                    'location_id' => $location_id,
                    'credential_id' => $credential->id,
                    'token_url' => $credential->api_url,
                    'production_url' => $additionalData['production_url'] ?? 'NOT SET',
                    'property_code' => $additionalData['property_code'] ?? 'CCB1',
                    'pos_interface_code' => $additionalData['pos_interface_code'] ?? $credential->client_id,
                    'additional_data_raw' => $credential->additional_data
                ]);

                $productionUrl = $additionalData['production_url'] ?? '';

                if (empty($productionUrl)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Production URL is not configured for this location. Please update the integration settings and add the Production URL.'
                    ]);
                }

                $apiService = new \Modules\Gallface\Services\GallfaceApiService([
                    'access_token_url' => $credential->api_url,
                    'production_url' => $productionUrl,
                    'client_id' => $credential->client_id,
                    'client_secret' => $credential->client_secret,
                    'property_code' => $additionalData['property_code'] ?? 'CCB1',
                    'pos_interface_code' => $additionalData['pos_interface_code'] ?? $credential->client_id
                ]);

                $result = $apiService->syncSales($salesData, $location_id);

                if ($result['success']) {
                    // Mark as synced - ONLY for this location's invoices
                    $invoiceNos = $salesData->pluck('invoice_no')->toArray();
                    \DB::table('transactions')
                        ->where('business_id', $business_id)
                        ->where('location_id', $location_id) // Additional safety check
                        ->whereIn('invoice_no', $invoiceNos)
                        ->update(['gallface_synced_at' => now()]);

                    // Update last synced timestamp
                    $credential->update(['last_synced_at' => now()]);

                    \Log::info('Gallface: Sales synced successfully', [
                        'location_id' => $location_id,
                        'location_name' => $location->name,
                        'records_synced' => $result['records_synced']
                    ]);
                }

                return response()->json($result);

            } catch (\Exception $e) {
                \Log::error('Gallface: Sync failed', [
                    'location_id' => $location_id,
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Sync failed: ' . $e->getMessage()
                ], 500);
            }
        }

                    /**
         * Send ping to Gallface API
         */
        public function sendGallfacePing($location_id)
        {
            try {
                $business_id = request()->session()->get('user.business_id');

                $credential = \Modules\Gallface\Models\LocationApiCredential::where('business_id', $business_id)
                    ->where('business_location_id', $location_id)
                    ->where('mall_code', 'gallface')
                    ->where('is_active', true)
                    ->firstOrFail();

                // Validate credentials
                if (empty($credential->client_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Client ID is missing. Please update your credentials.'
                    ]);
                }

                if (empty($credential->client_secret)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Client Secret is missing. Please update your credentials.'
                    ]);
                }

                if (empty($credential->api_url)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access Token URL is missing. Please update your credentials.'
                    ]);
                }

                $additionalData = json_decode($credential->additional_data ?? '{}', true);

                \Log::info('Gallface Ping: Credentials loaded', [
                    'location_id' => $location_id,
                    'api_url' => $credential->api_url,
                    'client_id' => $credential->client_id,
                    'has_client_secret' => !empty($credential->client_secret),
                    'additional_data' => $additionalData
                ]);

                $apiService = new \Modules\Gallface\Services\GallfaceApiService([
                    'access_token_url' => $credential->api_url,
                    'production_url' => $additionalData['production_url'] ?? '',
                    'client_id' => $credential->client_id,
                    'client_secret' => $credential->client_secret,
                    'property_code' => $additionalData['property_code'] ?? 'CCB1',
                    'pos_interface_code' => $additionalData['pos_interface_code'] ?? $credential->client_id
                ]);

                // Test connection serves as ping
                $result = $apiService->testConnection();

                return response()->json([
                    'success' => $result['success'],
                    'message' => $result['success'] ? 'Ping successful - API is reachable' : 'Ping failed: ' . $result['message']
                ]);

            } catch (\Exception $e) {
                \Log::error('Gallface Ping Exception', [
                    'location_id' => $location_id,
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Ping failed: ' . $e->getMessage()
                ], 500);
            }
        }

        /**
         * Delete Gallface API connection
         */
        public function deleteGallfaceApi($id)
        {
            try {
                $business_id = request()->session()->get('user.business_id');

                $deleted = \Modules\Gallface\Models\LocationApiCredential::where('business_id', $business_id)
                    ->where('id', $id)
                    ->where('mall_code', 'gallface')
                    ->delete();

                if ($deleted) {
                    return response()->json([
                        'success' => true,
                        'message' => 'API connection deleted successfully'
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Connection not found'
                ], 404);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete connection: ' . $e->getMessage()
                ], 500);
            }
        }

        /**
         * View Gallface invoice history
         */
        public function viewGallfaceInvoiceHistory(Request $request, $location_id)
        {
            try {
                $business_id = request()->session()->get('user.business_id');

                // Verify location belongs to business
                $location = \App\BusinessLocation::where('id', $location_id)
                    ->where('business_id', $business_id)
                    ->firstOrFail();

                $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
                $dateTo = $request->input('date_to', now()->format('Y-m-d'));
                $syncStatus = $request->input('sync_status', 'all');

                $query = \DB::table('transactions as t')
                    ->select(
                        't.id',
                        't.invoice_no',
                        't.transaction_date',
                        't.final_total',
                        't.tax_amount',
                        't.discount_amount',
                        't.gallface_synced_at',
                        't.type',
                        'c.name as customer_name',
                        'c.mobile as customer_mobile'
                    )
                    ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
                    ->where('t.business_id', $business_id)
                    ->where('t.location_id', $location_id)
                    ->where('t.type', 'sell')
                    ->whereBetween('t.transaction_date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->orderBy('t.transaction_date', 'desc');

                if ($syncStatus === 'synced') {
                    $query->whereNotNull('t.gallface_synced_at');
                } elseif ($syncStatus === 'not_synced') {
                    $query->whereNull('t.gallface_synced_at');
                }

                $invoices = $query->get();

                // Calculate stats
                $stats = [
                    'total_invoices' => \DB::table('transactions')
                        ->where('business_id', $business_id)
                        ->where('location_id', $location_id)
                        ->where('type', 'sell')
                        ->whereBetween('transaction_date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                        ->count(),
                    'synced_invoices' => \DB::table('transactions')
                        ->where('business_id', $business_id)
                        ->where('location_id', $location_id)
                        ->where('type', 'sell')
                        ->whereNotNull('gallface_synced_at')
                        ->whereBetween('transaction_date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                        ->count(),
                    'not_synced_invoices' => \DB::table('transactions')
                        ->where('business_id', $business_id)
                        ->where('location_id', $location_id)
                        ->where('type', 'sell')
                        ->whereNull('gallface_synced_at')
                        ->whereBetween('transaction_date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                        ->count(),
                ];

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'invoices' => ['data' => $invoices],
                        'stats' => $stats
                    ]);
                }

                return view('gallface::gallface.gallface_invoice_history', compact('invoices', 'stats', 'location_id'));

            } catch (\Exception $e) {
                \Log::error('Gallface Invoice History Error: ' . $e->getMessage());

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to fetch invoice history: ' . $e->getMessage()
                    ], 500);
                }
                return redirect()->back()->with('error', 'Failed to fetch invoice history: ' . $e->getMessage());
            }
        }

        /**
         * Get Gallface invoices with filters
         */
        public function getGallfaceInvoices(Request $request)
        {
            try {
                $business_id = request()->session()->get('user.business_id');

                if (!$business_id) {
                    \Log::error('Gallface Invoice Error: No business_id in session');
                    return response()->json([
                        'success' => false,
                        'message' => 'Session expired. Please refresh the page.'
                    ], 401);
                }

                \Log::info('Gallface Invoice Request', [
                    'business_id' => $business_id,
                    'params' => $request->all()
                ]);

                $locationId = $request->input('location_id');
                $syncStatus = $request->input('sync_status');
                $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
                $dateTo = $request->input('date_to', now()->format('Y-m-d'));

                // Validate location belongs to business if specified
                if ($locationId) {
                    $locationExists = \App\BusinessLocation::where('id', $locationId)
                        ->where('business_id', $business_id)
                        ->exists();

                    if (!$locationExists) {
                        \Log::warning('Gallface Invoice: Invalid location', ['location_id' => $locationId]);
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid location'
                        ], 403);
                    }
                }

                $query = \DB::table('transactions as t')
                    ->select(
                        't.id',
                        't.invoice_no',
                        't.transaction_date',
                        't.final_total',
                        't.tax_amount',
                        't.discount_amount',
                        't.gallface_synced_at',
                        't.type',
                        't.location_id',
                        'c.name as customer_name',
                        'c.mobile as customer_mobile',
                        'bl.name as location_name'
                    )
                    ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
                    ->leftJoin('business_locations as bl', 't.location_id', '=', 'bl.id')
                    ->where('t.business_id', $business_id)
                    ->where('t.type', 'sell')
                    ->whereBetween('t.transaction_date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->orderBy('t.transaction_date', 'desc');

                // Apply location filter - IMPORTANT: Only show invoices from selected location
                if ($locationId) {
                    $query->where('t.location_id', $locationId);
                }

                if ($syncStatus === 'synced') {
                    $query->whereNotNull('t.gallface_synced_at');
                } elseif ($syncStatus === 'pending') {
                    $query->whereNull('t.gallface_synced_at');
                }

                $invoices = $query->get();

                $invoicesArray = $invoices->map(function($invoice) {
                    return [
                        'id' => $invoice->id,
                        'invoice_no' => $invoice->invoice_no,
                        'transaction_date' => $invoice->transaction_date,
                        'final_total' => number_format((float)$invoice->final_total, 2),
                        'tax_amount' => number_format((float)$invoice->tax_amount, 2),
                        'discount_amount' => number_format((float)$invoice->discount_amount, 2),
                        'gallface_synced_at' => $invoice->gallface_synced_at,
                        'customer_name' => $invoice->customer_name ?? 'Walk-in Customer',
                        'customer_mobile' => $invoice->customer_mobile ?? '-',
                        'location_name' => $invoice->location_name ?? 'Unknown',
                        'location_id' => $invoice->location_id,
                        'type' => $invoice->type,
                        'sync_status' => $invoice->gallface_synced_at ? 'synced' : 'pending'
                    ];
                })->values()->toArray();

                \Log::info('Gallface Invoices Retrieved', [
                    'count' => count($invoicesArray),
                    'location_id' => $locationId,
                    'sync_status' => $syncStatus
                ]);

                // Standard DataTables response format
                return response()->json([
                    'draw' => intval($request->input('draw', 1)),
                    'recordsTotal' => count($invoicesArray),
                    'recordsFiltered' => count($invoicesArray),
                    'data' => $invoicesArray
                ]);
            } catch (\Exception $e) {
                \Log::error('Gallface Invoice Fetch Error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'request_data' => $request->all()
                ]);

                // DataTables error response
                return response()->json([
                    'draw' => intval($request->input('draw', 1)),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Failed to fetch invoices: ' . $e->getMessage()
                ], 200);
            }
        }

        /**
         * Delete HCM credentials
         */
        public function deleteHcmCredentials($location_id)
        {
            try {
                $business_id = request()->session()->get('user.business_id');

                $deleted = \Modules\Gallface\Models\LocationApiCredential::where('business_id', $business_id)
                    ->where('business_location_id', $location_id)
                    ->where('mall_code', 'hcm')
                    ->delete();

                if ($deleted) {
                    return response()->json([
                        'success' => true,
                        'message' => 'HCM configuration deleted successfully'
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Configuration not found'
                ], 404);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete configuration: ' . $e->getMessage()
                ], 500);
            }
        }

        /**
         * Show HCM credentials management page
         */
        public function hcmCredentials()
        {
            $business_id = request()->session()->get('user.business_id');
            $locations = \App\BusinessLocation::where('business_id', $business_id)
                ->where('is_active', true)
                ->with(['credentials' => function($query) {
                    $query->where('mall_code', 'hcm');
                }])
                ->get();

            // Get statistics
            $activeIntegrations = \Modules\Gallface\Models\LocationApiCredential::where('business_id', $business_id)
                ->where('mall_code', 'hcm')
                ->where('is_active', true)
                ->count();

            $recentSyncs = \DB::table('transactions')
                ->where('business_id', $business_id)
                ->whereNotNull('hcm_synced_at')
                ->whereDate('hcm_synced_at', today())
                ->count();

            $lastSync = \Modules\Gallface\Models\LocationApiCredential::where('business_id', $business_id)
                ->where('mall_code', 'hcm')
                ->whereNotNull('last_synced_at')
                ->orderBy('last_synced_at', 'desc')
                ->first();

            $lastSyncTime = $lastSync ? $lastSync->last_synced_at->diffForHumans() : 'Never';

            return view('gallface::gallface.hcm_credentials', compact(
                'locations', 
                'activeIntegrations', 
                'recentSyncs', 
                'lastSyncTime'
            ));
        }

        /**
         * Save HCM credentials for a location
         */
        public function saveHcmCredentials(Request $request, $location_id)
        {
            try {
                $business_id = request()->session()->get('user.business_id');

                $validated = $request->validate([
                    'business_location_id' => 'required_if:location_id,0',
                    'api_url' => 'required|url',
                    'username' => 'required|string',
                    'password' => 'required|string',
                    'stall_no' => 'required|string',
                    'pos_id' => 'required|string'
                ]);

                // If location_id is 0, use the one from the form (for new credentials)
                if ($location_id == 0 && isset($validated['business_location_id'])) {
                    $location_id = $validated['business_location_id'];
                }

                // Validate that location belongs to business
                $location = \App\BusinessLocation::where('id', $location_id)
                    ->where('business_id', $business_id)
                    ->first();

                if (!$location) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid business location'
                        ], 404);
                    }
                    return redirect()->back()->with('error', 'Invalid business location');
                }

                // Create or update credential (location-specific)
                $credential = \Modules\Gallface\Models\LocationApiCredential::updateOrCreate(
                    [
                        'business_id' => $business_id,
                        'business_location_id' => $location_id,
                        'mall_code' => 'hcm'
                    ],
                    [
                        'api_url' => $request->api_url,
                        'username' => $request->username,
                        'password' => $request->password,
                        'stall_no' => $request->stall_no,
                        'pos_id' => $request->pos_id,
                        'sync_type' => $request->sync_type ?? 'manual',
                        'is_active' => $request->has('is_active') ? true : false
                    ]
                );

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'HCM credentials saved successfully'
                    ]);
                }

                return redirect()->back()->with('success', 'HCM credentials saved successfully');
            } catch (\Exception $e) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to save credentials: ' . $e->getMessage()
                    ], 500);
                }
                return redirect()->back()->with('error', 'Failed to save credentials: ' . $e->getMessage());
            }
        }


        /**
         * Get auto-sync monitor status
         */
        public function getMonitorStatus()
        {
            try {
                $business_id = request()->session()->get('user.business_id');

                // Get all active credentials
                $credentials = LocationApiCredential::with('businessLocation')
                    ->where('business_id', $business_id)
                    ->where('is_active', true)
                    ->get();

                $locations = [];
                foreach ($credentials as $cred) {
                    $pendingSales = DB::table('transactions')
                        ->where('business_id', $business_id)
                        ->where('location_id', $cred->business_location_id)
                        ->where('type', 'sell')
                        ->when($cred->mall_code === 'gallface', function($q) {
                            return $q->whereNull('gallface_synced_at');
                        })
                        ->when($cred->mall_code === 'hcm', function($q) {
                            return $q->whereNull('hcm_synced_at');
                        })
                        ->count();

                    $locations[] = [
                        'name' => $cred->businessLocation->name ?? 'Unknown',
                        'mall_code' => $cred->mall_code,
                        'auto_sync_enabled' => $cred->auto_sync_enabled,
                        'last_synced_at' => $cred->last_synced_at ? $cred->last_synced_at->diffForHumans() : null,
                        'pending_sales' => $pendingSales
                    ];
                }

                // Get recent logs (last 10 entries)
                $gallfaceLogs = \Log::getLogger()->getHandlers()[0] ?? null;
                $hcmLogs = \Log::getLogger()->getHandlers()[0] ?? null;

                return response()->json([
                    'success' => true,
                    'gallface' => [
                        'last_sync' => LocationApiCredential::where('mall_code', 'gallface')
                            ->where('auto_sync_enabled', true)
                            ->max('last_synced_at'),
                        'logs' => 'Auto-sync active'
                    ],
                    'hcm' => [
                        'last_sync' => LocationApiCredential::where('mall_code', 'hcm')
                            ->where('auto_sync_enabled', true)
                            ->max('last_synced_at'),
                        'logs' => 'Auto-sync active'
                    ],
                    'locations' => $locations
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        }
    }