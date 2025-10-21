
<?php

namespace Modules\HCMIntegration\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HCMIntegration\Entities\HcmTenantConfig;
use Modules\HCMIntegration\Entities\HcmInvoiceLog;
use Modules\HCMIntegration\Utils\HcmApiUtil;
use Yajra\DataTables\Facades\DataTables;

class HcmIntegrationController extends Controller
{
    /**
     * Display module dashboard
     */
    public function index()
    {
        if (!auth()->user()->can('superadmin') && !auth()->user()->can('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $configs = HcmTenantConfig::where('business_id', $business_id)
            ->with('location')
            ->get();

        return view('hcmintegration::index', compact('configs'));
    }

    /**
     * Show configuration form
     */
    public function create()
    {
        if (!auth()->user()->can('superadmin') && !auth()->user()->can('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $locations = \App\BusinessLocation::where('business_id', $business_id)->pluck('name', 'id');
        
        // Exclude already configured locations
        $configuredLocations = HcmTenantConfig::where('business_id', $business_id)->pluck('location_id')->toArray();
        $locations = $locations->except($configuredLocations);

        return view('hcmintegration::create', compact('locations'));
    }

    /**
     * Store new configuration
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('superadmin') && !auth()->user()->can('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'location_id' => 'required|exists:business_locations,id',
            'username' => 'required',
            'password' => 'required',
            'stall_no' => 'required',
            'pos_id' => 'required',
        ]);

        $business_id = request()->session()->get('user.business_id');

        // Check if location already configured
        $exists = HcmTenantConfig::where('business_id', $business_id)
            ->where('location_id', $request->location_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'msg' => 'This location is already configured'
            ]);
        }

        $config = HcmTenantConfig::create([
            'business_id' => $business_id,
            'location_id' => $request->location_id,
            'username' => $request->username,
            'password' => $request->password,
            'stall_no' => $request->stall_no,
            'pos_id' => $request->pos_id,
            'api_url' => $request->api_url ?? 'https://trms-api.azurewebsites.net',
            'is_active' => $request->is_active ?? true,
            'auto_sync' => $request->auto_sync ?? true,
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Configuration saved successfully'
        ]);
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        if (!auth()->user()->can('superadmin') && !auth()->user()->can('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $config = HcmTenantConfig::where('business_id', $business_id)->findOrFail($id);
        $locations = \App\BusinessLocation::where('business_id', $business_id)->pluck('name', 'id');

        return view('hcmintegration::edit', compact('config', 'locations'));
    }

    /**
     * Update configuration
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('superadmin') && !auth()->user()->can('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'stall_no' => 'required',
            'pos_id' => 'required',
        ]);

        $business_id = request()->session()->get('user.business_id');
        $config = HcmTenantConfig::where('business_id', $business_id)->findOrFail($id);

        $config->update([
            'username' => $request->username,
            'password' => $request->password,
            'stall_no' => $request->stall_no,
            'pos_id' => $request->pos_id,
            'api_url' => $request->api_url ?? 'https://trms-api.azurewebsites.net',
            'is_active' => $request->is_active ?? true,
            'auto_sync' => $request->auto_sync ?? true,
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Configuration updated successfully'
        ]);
    }

    /**
     * Delete configuration
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('superadmin') && !auth()->user()->can('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $config = HcmTenantConfig::where('business_id', $business_id)->findOrFail($id);
        $config->delete();

        return response()->json([
            'success' => true,
            'msg' => 'Configuration deleted successfully'
        ]);
    }

    /**
     * Test connection
     */
    public function testConnection($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $config = HcmTenantConfig::where('business_id', $business_id)->findOrFail($id);

        $api = new HcmApiUtil($config);
        $result = $api->testConnection();

        return response()->json($result);
    }
}
