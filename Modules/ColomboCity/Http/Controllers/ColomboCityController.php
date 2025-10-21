
<?php

namespace Modules\ColomboCity\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\BusinessLocation;

class ColomboCityController extends Controller
{
    /**
     * Show Colombo City Center dashboard
     */
    public function dashboard()
    {
        $business_id = request()->session()->get('user.business_id');

        // Get business locations
        $locations = BusinessLocation::where('business_id', $business_id)
            ->where('is_active', true)
            ->get();

        // Get statistics
        $totalTransactions = DB::table('colombo_city_transactions')->count();
        $todayTransactions = DB::table('colombo_city_transactions')
            ->whereDate('business_date', today())
            ->count();
        $todaySales = DB::table('colombo_city_transactions')
            ->whereDate('business_date', today())
            ->where('transaction_status', 'SALES')
            ->sum('invoice_amount');

        return view('colombocity::dashboard', compact(
            'locations',
            'totalTransactions',
            'todayTransactions',
            'todaySales'
        ));
    }

    /**
     * Show configuration page
     */
    public function configuration()
    {
        $business_id = request()->session()->get('user.business_id');

        $locations = BusinessLocation::where('business_id', $business_id)
            ->where('is_active', true)
            ->get();

        return view('colombocity::configuration', compact('locations'));
    }

    /**
     * Save location mapping
     */
    public function saveLocationMapping(Request $request)
    {
        $request->validate([
            'business_location_id' => 'required|exists:business_locations,id',
            'colombo_location_code' => 'required|string'
        ]);

        $business_id = request()->session()->get('user.business_id');

        DB::table('colombo_city_location_mapping')->updateOrInsert(
            [
                'business_id' => $business_id,
                'business_location_id' => $request->business_location_id
            ],
            [
                'colombo_location_code' => $request->colombo_location_code,
                'updated_at' => now()
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Location mapping saved successfully'
        ]);
    }

    /**
     * Get transactions report
     */
    public function getTransactions(Request $request)
    {
        $from_date = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $to_date = $request->input('to_date', now()->format('Y-m-d'));
        $location_code = $request->input('location_code');

        $query = DB::table('colombo_city_transactions as t')
            ->select(
                't.receipt_num',
                't.business_date',
                't.receipt_time',
                't.invoice_amount',
                't.tax_amount',
                't.transaction_status',
                't.location_code',
                't.terminal_id'
            )
            ->whereBetween('t.business_date', [$from_date, $to_date])
            ->orderBy('t.business_date', 'desc')
            ->orderBy('t.receipt_time', 'desc');

        if ($location_code) {
            $query->where('t.location_code', $location_code);
        }

        $transactions = $query->get();

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }
}
