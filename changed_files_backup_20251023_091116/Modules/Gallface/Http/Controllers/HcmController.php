<?php

namespace Modules\Gallface\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Gallface\Models\LocationApiCredential;
use Modules\Gallface\Services\HcmApiService;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Gallface\Exports\HcmSalesExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HcmController extends Controller
{
    /**
     * View invoice history for HCM sync
     */
    public function viewInvoiceHistory(Request $request, $location_id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            // Get filter parameters
            $syncStatus = $request->input('sync_status', 'all'); // all, synced, not_synced
            $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
            $dateTo = $request->input('date_to', now()->format('Y-m-d'));
            $perPage = $request->input('per_page', 50);
            
            // Build the query
            $query = DB::table('transactions as t')
                ->select(
                    't.id',
                    't.invoice_no',
                    't.transaction_date',
                    't.final_total',
                    't.tax_amount',
                    't.discount_amount',
                    't.hcm_synced_at',
                    't.type',
                    'c.name as customer_name',
                    'c.mobile as customer_mobile',
                    't.is_gift_voucher'
                )
                ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
                ->where('t.business_id', $business_id)
                ->where('t.location_id', $location_id)
                ->where('t.type', 'sell')
                ->whereBetween('t.transaction_date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->orderBy('t.transaction_date', 'desc');
            
            // Apply sync status filter
            if ($syncStatus === 'synced') {
                $query->whereNotNull('t.hcm_synced_at');
            } elseif ($syncStatus === 'not_synced') {
                $query->whereNull('t.hcm_synced_at');
            }
            
            // Get stats
            $totalInvoices = DB::table('transactions')
                ->where('business_id', $business_id)
                ->where('location_id', $location_id)
                ->where('type', 'sell')
                ->whereBetween('transaction_date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->count();
            
            $syncedInvoices = DB::table('transactions')
                ->where('business_id', $business_id)
                ->where('location_id', $location_id)
                ->where('type', 'sell')
                ->whereNotNull('hcm_synced_at')
                ->whereBetween('transaction_date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->count();
            
            $stats = [
                'total_invoices' => $totalInvoices,
                'synced_invoices' => $syncedInvoices,
                'not_synced_invoices' => $totalInvoices - $syncedInvoices
            ];
            
            // If AJAX request, return JSON
            if ($request->expectsJson() || $request->ajax()) {
                $invoices = $query->get();
                return response()->json([
                    'success' => true,
                    'invoices' => ['data' => $invoices],
                    'stats' => $stats
                ]);
            }

            // Return view for non-AJAX requests
            $invoices = $query->paginate($perPage);
            return view('gallface::gallface.hcm_invoice_history', compact('invoices', 'stats', 'location_id'));

        } catch (\Exception $e) {
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
     * Show ping monitor interface
     */
    public function showPingMonitor($location_id)
    {
        $business_id = request()->session()->get('user.business_id');
        
        $location = DB::table('business_locations')
            ->where('id', $location_id)
            ->where('business_id', $business_id)
            ->first();

        if (!$location) {
            return redirect()->back()->with('error', 'Location not found');
        }

        return view('gallface::gallface.hcm_ping_monitor', compact('location', 'location_id'));
    }

    /**
     * Test HCM API connection
     */
    public function testConnection(Request $request, $location_id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            $credentials = LocationApiCredential::where('business_id', $business_id)
                ->where('business_location_id', $location_id)
                ->where('mall_code', 'hcm')
                ->where('is_active', true)
                ->first();

            if (!$credentials) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active HCM credentials found'
                ]);
            }

            $apiService = new HcmApiService($credentials->getCredentialsForApi());
            return response()->json($apiService->testConnection());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Send ping to HCM API with user info
     */
    public function sendPing(Request $request, $location_id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $userId = request()->session()->get('user.id');
            $username = request()->session()->get('user.username') ?? request()->session()->get('user.first_name') ?? 'Unknown';
            $ipAddress = $request->ip();

            $credentials = LocationApiCredential::where('business_id', $business_id)
                ->where('business_location_id', $location_id)
                ->where('mall_code', 'hcm')
                ->where('is_active', true)
                ->first();

            if (!$credentials) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active HCM credentials found'
                ]);
            }

            $apiService = new HcmApiService($credentials->getCredentialsForApi());
            $result = $apiService->sendPing($userId, $username, $ipAddress);

            // Log ping to database
            DB::table('hcm_ping_logs')->insert([
                'location_id' => $location_id,
                'user_id' => $userId,
                'username' => $username,
                'ip_address' => $ipAddress,
                'tenant_id' => $credentials->username,
                'pos_id' => $credentials->pos_id,
                'success' => $result['success'],
                'message' => $result['message'],
                'response_data' => isset($result['response']) ? json_encode($result['response']) : null,
                'pinged_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ping failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get ping logs for real-time monitoring
     */
    public function getPingLogs(Request $request, $location_id)
    {
        try {
            $limit = $request->input('limit', 50);
            $since = $request->input('since'); // timestamp to get logs after

            $query = DB::table('hcm_ping_logs')
                ->where('location_id', $location_id)
                ->orderBy('created_at', 'desc');

            if ($since) {
                $query->where('created_at', '>', $since);
            }

            $logs = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'logs' => $logs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch ping logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync sales data to HCM in real-time
     */
    public function syncSales(Request $request, $location_id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            $credentials = LocationApiCredential::where('business_id', $business_id)
                ->where('business_location_id', $location_id)
                ->where('mall_code', 'hcm')
                ->where('is_active', true)
                ->first();

            if (!$credentials) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active HCM credentials found for this location'
                ], 404);
            }

            $apiService = new HcmApiService($credentials->getCredentialsForApi());

            // Get unsynced sales data with payment lines
            $salesData = DB::table('transactions as t')
                ->select('t.*', 'c.mobile', 'u.username as cashier_name')
                ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
                ->leftJoin('users as u', 't.created_by', '=', 'u.id')
                ->where('t.business_id', $business_id)
                ->where('t.location_id', $location_id)
                ->where('t.type', 'sell')
                ->whereNull('t.hcm_synced_at')
                ->limit(100)
                ->get();

            if ($salesData->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No new sales to sync'
                ]);
            }

            // Enrich with payment lines
            foreach ($salesData as $sale) {
                $paymentLines = DB::table('transaction_payments')
                    ->where('transaction_id', $sale->id)
                    ->get()
                    ->toArray();
                $sale->payment_lines = $paymentLines;
            }

            $result = $apiService->syncSales($salesData->toArray(), $location_id);

            // Mark as synced if any were successful
            if ($result['success'] && $result['records_synced'] > 0) {
                $syncedInvoices = [];
                foreach ($salesData as $index => $sale) {
                    // Check if this specific invoice was synced (not in errors)
                    $hasError = false;
                    if (isset($result['errors'])) {
                        foreach ($result['errors'] as $error) {
                            if ($error['invoice_no'] === $sale->invoice_no) {
                                $hasError = true;
                                break;
                            }
                        }
                    }
                    if (!$hasError) {
                        $syncedInvoices[] = $sale->invoice_no;
                    }
                }

                if (!empty($syncedInvoices)) {
                    DB::table('transactions')
                        ->whereIn('invoice_no', $syncedInvoices)
                        ->update(['hcm_synced_at' => now()]);
                }
            }

            // Log errors for debugging
            if (isset($result['errors']) && !empty($result['errors'])) {
                Log::error('HCM Sync Errors', [
                    'location_id' => $location_id,
                    'errors' => $result['errors']
                ]);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('HCM Sync Exception', [
                'location_id' => $location_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate and download Excel for manual upload
     */
    public function downloadExcel(Request $request, $location_id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $reportType = $request->input('report_type', 'daily'); // daily or monthly
            $startDate = $request->input('start_date', now()->startOfDay());
            $endDate = $request->input('end_date', now()->endOfDay());

            $fileName = 'HCM_Sales_' . $reportType . '_' . now()->format('Y-m-d') . '.xlsx';

            return Excel::download(
                new HcmSalesExport($business_id, $location_id, $startDate, $endDate),
                $fileName
            );

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Excel generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual sync test - sends a test invoice to verify sync works
     */
    public function manualSyncTest(Request $request, $location_id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            $credentials = LocationApiCredential::where('business_id', $business_id)
                ->where('business_location_id', $location_id)
                ->where('mall_code', 'hcm')
                ->where('is_active', true)
                ->first();

            if (!$credentials) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active HCM credentials found for this location'
                ], 404);
            }

            $apiService = new HcmApiService($credentials->getCredentialsForApi());

            // Get one unsynced sale to test
            $salesData = DB::table('transactions as t')
                ->select('t.*', 'c.mobile', 'u.username as cashier_name')
                ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
                ->leftJoin('users as u', 't.created_by', '=', 'u.id')
                ->where('t.business_id', $business_id)
                ->where('t.location_id', $location_id)
                ->where('t.type', 'sell')
                ->whereNull('t.hcm_synced_at')
                ->limit(1)
                ->get();

            if ($salesData->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No unsynced sales found to test. All invoices are already synced.'
                ]);
            }

            // Enrich with payment lines
            foreach ($salesData as $sale) {
                $paymentLines = DB::table('transaction_payments')
                    ->where('transaction_id', $sale->id)
                    ->get()
                    ->toArray();
                $sale->payment_lines = $paymentLines;
            }

            Log::info('Manual Sync Test Started', [
                'location_id' => $location_id,
                'invoice_no' => $salesData[0]->invoice_no,
                'credentials' => [
                    'tenant_id' => $credentials->username,
                    'pos_id' => $credentials->pos_id
                ]
            ]);

            $result = $apiService->syncSales($salesData->toArray(), $location_id);

            // Mark as synced if successful
            if ($result['success'] && $result['records_synced'] > 0) {
                DB::table('transactions')
                    ->where('invoice_no', $salesData[0]->invoice_no)
                    ->update(['hcm_synced_at' => now()]);
                
                Log::info('Manual Sync Test - Invoice marked as synced', [
                    'invoice_no' => $salesData[0]->invoice_no
                ]);
            }

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'details' => $result,
                'test_invoice' => [
                    'invoice_no' => $salesData[0]->invoice_no,
                    'transaction_date' => $salesData[0]->transaction_date,
                    'final_total' => $salesData[0]->final_total
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Manual Sync Test Exception', [
                'location_id' => $location_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sync test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload Excel data to HCM
     */
    public function uploadExcel(Request $request, $location_id)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $credentials = LocationApiCredential::where('business_id', $business_id)
                ->where('business_location_id', $location_id)
                ->where('mall_code', 'hcm')
                ->where('is_active', true)
                ->first();

            if (!$credentials) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active HCM credentials found'
                ]);
            }

            // Parse Excel file
            $excelData = Excel::toArray(new \stdClass(), $request->file('excel_file'));

            $apiService = new HcmApiService($credentials->getCredentialsForApi());
            return response()->json($apiService->uploadExcelData($excelData));

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Excel upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
}