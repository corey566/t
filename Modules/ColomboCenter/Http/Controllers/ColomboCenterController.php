
<?php

namespace Modules\ColomboCenter\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Business;

class ColomboCenterController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'colombocenter_module'))) {
            abort(403, 'Unauthorized action.');
        }

        return view('colombocenter::index');
    }

    /**
     * Show the dashboard.
     * @return Renderable
     */
    public function dashboard()
    {
        $business_id = request()->session()->get('user.business_id');

        return view('colombocenter::dashboard', compact('business_id'));
    }
}
