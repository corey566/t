
<?php

namespace Modules\GallfaceMims\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Business;

class GallfaceMimsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        return view('gallfacemims::index');
    }

    /**
     * Show the dashboard.
     * @return Renderable
     */
    public function dashboard()
    {
        $business_id = request()->session()->get('user.business_id');

        return view('gallfacemims::dashboard', compact('business_id'));
    }
}
