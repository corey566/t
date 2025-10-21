<?php

namespace Modules\Hcm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Hcm\Entities\HcmTenantConfig;
use Modules\Hcm\Utils\HcmUtil;
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
            $syncStatus = $request->input('sync_status', 'all');
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
                ->whereIn('t.type', ['sell', 'sell_return'])
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
                ->whereIn('type', ['sell', 'sell_return'])
                ->whereBetween('transaction_date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->count();

            $syncedInvoices = DB::table('transactions')
                ->where('business_id', $business_id)
                ->where('location_id', $location_id)
                ->whereIn('type', ['sell', 'sell_return'])
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
            return view('hcm::hcm.invoice_history', compact('invoices', 'stats', 'location_id'));

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

        return view('hcm::hcm.ping_monitor', compact('location', 'location_id'));
    }

    /**
     * Test HCM API connection
     */
    public function testConnection(Request $request, $location_id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            $config = HcmTenantConfig::where('business_id', $business_id)
                ->where('business_location_id', $location_id)
                ->where('is_active', true)
                ->first();

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active HCM configuration found'
                ]);
            }

            $hcmUtil = new HcmUtil();
            return response()->json($hcmUtil->testConnection($config));

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

            $config = HcmTenantConfig::where('business_id', $business_id)
                ->where('business_location_id', $location_id)
                ->where('is_active', true)
                ->first();

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active HCM configuration found'
                ]);
            }

            $hcmUtil = new HcmUtil();
            $result = $hcmUtil->sendPing($config, $userId, $username, $ipAddress);

            // Log ping to database
            DB::table('hcm_ping_logs')->insert([
                'location_id' => $location_id,
                'user_id' => $userId,
                'username' => $username,
                'ip_address' => $ipAddress,
                'tenant_id' => $config->tenant_id,
                'pos_id' => $config->pos_id,
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
            $since = $request->input('since');

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
     * Sync sales data to HCM
     */
    public function syncSales(Request $request, $location_id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            $config = HcmTenantConfig::where('business_id', $business_id)
                ->where('business_location_id', $location_id)
                ->where('is_active', true)
                ->first();

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active HCM configuration found for this location'
                ], 404);
            }

            $hcmUtil = new HcmUtil();
            $result = $hcmUtil->syncInvoices($config);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('HCM Sync Error', [
                'location_id' => $location_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }
}