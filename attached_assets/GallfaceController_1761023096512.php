<?php

namespace Modules\Gallface\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Business;

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
        return view('gallface::gallface.setting',compact('gallfacessetting'));
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
}
