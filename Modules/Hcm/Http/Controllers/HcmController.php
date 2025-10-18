
<?php

namespace Modules\Hcm\Http\Controllers;

use App\Business;
use App\BusinessLocation;
use App\System;
use App\Transaction;
use App\Utils\ModuleUtil;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Hcm\Entities\HcmTenantConfig;
use Modules\Hcm\Entities\HcmInvoiceLog;
use Modules\Hcm\Entities\HcmPingLog;
use Modules\Hcm\Entities\HcmSyncLog;
use Modules\Hcm\Utils\HcmUtil;
use Yajra\DataTables\Facades\DataTables;

class HcmController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $hcmUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  HcmUtil  $hcmUtil
     * @return void
     */
    public function __construct(HcmUtil $hcmUtil, ModuleUtil $moduleUtil)
    {
        $this->hcmUtil = $hcmUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $business_id = request()->session()->get('business.id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hcm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $alerts = [];

        // Get pending invoices count
        $pending_invoices = HcmInvoiceLog::where('business_id', $business_id)
                                      ->where('status', 'pending')
                                      ->count();

        if ($pending_invoices > 0) {
            $alerts['pending_invoices'] = $pending_invoices . ' invoices pending sync';
        }

        // Get failed invoices count
        $failed_invoices = HcmInvoiceLog::where('business_id', $business_id)
                                     ->where('status', 'failed')
                                     ->count();

        if ($failed_invoices > 0) {
            $alerts['failed_invoices'] = $failed_invoices . ' invoices failed to sync';
        }

        // Get last sync status
        $last_sync = $this->hcmUtil->getLastSync($business_id, 'invoices', false);

        return view('hcm::hcm.index')
                ->with(compact('alerts', 'last_sync'));
    }

    /**
     * Displays form to configure tenant settings.
     *
     * @return Response
     */
    public function tenantConfig()
    {
        $business_id = request()->session()->get('business.id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hcm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $locations = BusinessLocation::forDropdown($business_id);
        $configs = HcmTenantConfig::where('business_id', $business_id)->get();
        $module_version = System::getProperty('hcm_version');

        return view('hcm::hcm.tenant_config')
                ->with(compact('locations', 'configs', 'module_version'));
    }

    /**
     * Updates tenant configuration.
     *
     * @return Response
     */
    public function updateTenantConfig(Request $request)
    {
        $business_id = request()->session()->get('business.id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hcm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->all();

            foreach ($input['configs'] as $location_id => $config) {
                HcmTenantConfig::updateOrCreate(
                    [
                        'business_id' => $business_id,
                        'location_id' => $location_id
                    ],
                    [
                        'tenant_id' => $config['tenant_id'],
                        'tenant_secret' => $config['tenant_secret'],
                        'api_url' => $config['api_url'] ?? config('hcm.api.base_url'),
                        'pos_id' => $config['pos_id'],
                        'stall_no' => $config['stall_no'] ?? null,
                        'active' => isset($config['active']) ? 1 : 0,
                        'auto_sync' => isset($config['auto_sync']) ? 1 : 0,
                        'retry_attempts' => $config['retry_attempts'] ?? 3,
                    ]
                );
            }

            $output = ['success' => 1,
                'msg' => 'Tenant configuration updated successfully',
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => 'Something went wrong',
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * Test connection with HCM API
     *
     * @return Response
     */
    public function testConnection()
    {
        $business_id = request()->session()->get('business.id');
        $location_id = request()->get('location_id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hcm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $result = $this->hcmUtil->testConnection($business_id, $location_id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * Synchronizes invoices with HCM
     *
     * @return Response
     */
    public function syncInvoices()
    {
        $business_id = request()->session()->get('business.id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hcm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();
            $user_id = request()->session()->get('user.id');

            $result = $this->hcmUtil->syncInvoices($business_id, $user_id);

            DB::commit();

            $output = ['success' => 1,
                'msg' => 'Invoices synced successfully',
                'synced_count' => $result['synced_count'] ?? 0,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return $output;
    }

    /**
     * Display synced invoices
     *
     * @return Response
     */
    public function syncedInvoices()
    {
        $business_id = request()->session()->get('business.id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hcm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $logs = HcmInvoiceLog::where('hcm_invoice_logs.business_id', $business_id)
                    ->leftjoin('business_locations as BL', 'BL.id', '=', 'hcm_invoice_logs.location_id')
                    ->leftjoin('transactions as T', 'T.id', '=', 'hcm_invoice_logs.transaction_id')
                    ->select([
                        'hcm_invoice_logs.id',
                        'hcm_invoice_logs.invoice_no',
                        'BL.name as location_name',
                        'hcm_invoice_logs.status',
                        'hcm_invoice_logs.response_message',
                        'hcm_invoice_logs.retry_count',
                        'hcm_invoice_logs.synced_at',
                        'T.final_total',
                        'hcm_invoice_logs.id as DT_RowId',
                    ]);

            return Datatables::of($logs)
                ->editColumn('status', function ($row) {
                    $class = '';
                    switch ($row->status) {
                        case 'success':
                            $class = 'label-success';
                            break;
                        case 'failed':
                            $class = 'label-danger';
                            break;
                        case 'pending':
                            $class = 'label-warning';
                            break;
                        case 'retrying':
                            $class = 'label-info';
                            break;
                    }
                    return '<span class="label ' . $class . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $html = '';
                    if ($row->status == 'failed') {
                        $html .= '<button type="button" class="btn btn-xs btn-primary retry-invoice" data-href="' . action([\Modules\Hcm\Http\Controllers\HcmController::class, 'retryFailedInvoice'], ['id' => $row->id]) . '">Retry</button>';
                    }
                    return $html;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('hcm::hcm.synced_invoices');
    }

    /**
     * Generate reports
     *
     * @return Response
     */
    public function reports()
    {
        $business_id = request()->session()->get('business.id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hcm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $locations = BusinessLocation::forDropdown($business_id);

        return view('hcm::hcm.reports')
                ->with(compact('locations'));
    }

    /**
     * Generate Excel report
     *
     * @return Response
     */
    public function generateReport(Request $request)
    {
        $business_id = request()->session()->get('business.id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hcm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $report = $this->hcmUtil->generateExcelReport(
                $business_id,
                $request->report_type,
                $request->location_id,
                $request->start_date,
                $request->end_date
            );

            return $report;
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display ping monitor
     *
     * @return Response
     */
    public function pingMonitor()
    {
        $business_id = request()->session()->get('business.id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hcm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $ping_logs = HcmPingLog::where('business_id', $business_id)
                              ->with('location')
                              ->orderBy('last_ping_at', 'desc')
                              ->get();

        return view('hcm::hcm.ping_monitor')
                ->with(compact('ping_logs'));
    }

    /**
     * Retry failed invoice
     *
     * @param  int  $id
     * @return Response
     */
    public function retryFailedInvoice($id)
    {
        $business_id = request()->session()->get('business.id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hcm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $result = $this->hcmUtil->retryFailedInvoice($id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get sync log
     *
     * @return Response
     */
    public function getSyncLog()
    {
        $business_id = request()->session()->get('business.id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hcm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $last_sync = [
                'invoices' => $this->hcmUtil->getLastSync($business_id, 'invoices'),
                'ping' => $this->hcmUtil->getLastSync($business_id, 'ping'),
                'reports' => $this->hcmUtil->getLastSync($business_id, 'reports'),
            ];

            return $last_sync;
        }
    }

    /**
     * View sync log
     *
     * @return Response
     */
    public function viewSyncLog()
    {
        $business_id = request()->session()->get('business.id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hcm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $logs = HcmSyncLog::where('hcm_sync_logs.business_id', $business_id)
                    ->leftjoin('users as U', 'U.id', '=', 'hcm_sync_logs.created_by')
                    ->select([
                        'hcm_sync_logs.created_at',
                        'sync_type', 'operation_type',
                        DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"),
                        'hcm_sync_logs.data',
                        'hcm_sync_logs.details as log_details',
                        'hcm_sync_logs.id as DT_RowId',
                    ]);

            return Datatables::of($logs)
                ->editColumn('created_at', function ($row) {
                    return \Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)->format('Y-m-d H:i:s');
                })
                ->editColumn('sync_type', function ($row) {
                    return ucfirst($row->sync_type);
                })
                ->editColumn('operation_type', function ($row) {
                    return ucfirst($row->operation_type);
                })
                ->rawColumns(['created_at'])
                ->make(true);
        }

        return view('hcm::hcm.sync_log');
    }

    /**
     * Get log details
     *
     * @param  int  $id
     * @return Response
     */
    public function getLogDetails($id)
    {
        $business_id = request()->session()->get('business.id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'hcm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $log = HcmSyncLog::where('business_id', $business_id)->find($id);
            $log_details = json_decode($log->details);

            return view('hcm::hcm.partials.log_details')
                    ->with(compact('log_details'));
        }
    }
}
