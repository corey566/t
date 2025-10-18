<?php

namespace Modules\Connector\Utils;

use App\BusinessLocation;
use App\SellingPriceGroup;
use Illuminate\Support\Facades\DB;


class PurchaseUtil extends Util {

    // protected $purchaseUtil ; 

    // public function __construct(PurchaseUtil $purchaseUtil)
    // {
    //     $this->purchaseUtil = $purchaseUtil ;
    // }




    public function permitted_locations_api($business_id)
    {
        $user = auth('api')->user();

        if ($user->can('access_all_locations')) {
            return 'all';
        } else {
            

            $permitted_locations = [];
            $all_locations = BusinessLocation::where('business_id', $business_id)->get();
            foreach ($all_locations as $location) {
                if ($user->can('location.'.$location->id)) {
                    $permitted_locations[] = $location->id;
                }
            }

            return $permitted_locations;
        }
    }

    public static function forDropdownApi($business_id, $show_all = false, $receipt_printer_type_attribute = false, $append_id = true, $check_permission = true)
    {
        $purchaseUtil = new PurchaseUtil;
        $user = auth('api')->user();

        $query = BusinessLocation::where('business_id', $business_id)->Active();

        
        if ($check_permission) {
            $permitted_locations = $purchaseUtil->permitted_locations_api($business_id);
            if ($permitted_locations != 'all') {
                $query->whereIn('id', $permitted_locations);
            }
        }
        // dd($permitted_locations);

        if ($append_id) {
            $query->select(
                DB::raw("IF(location_id IS NULL OR location_id='', name, CONCAT(name, ' (', location_id, ')')) AS name"),
                'id',
                'receipt_printer_type',
                'selling_price_group_id',
                'default_payment_accounts',
                'invoice_scheme_id',
                'invoice_layout_id',
                'sale_invoice_scheme_id'
            );
        }

        $result = $query->get();

        $locations = $result->pluck('name', 'id');

        $price_groups = self::SellingPriceforDropdownApi($business_id);

        if ($show_all) {
            $locations->prepend(__('report.all_locations'), '');
        }

        if ($receipt_printer_type_attribute) {
            $attributes = collect($result)->mapWithKeys(function ($item) use ($price_groups) {
                $default_payment_accounts = json_decode($item->default_payment_accounts, true);
                $default_payment_accounts['advance'] = [
                    'is_enabled' => 1,
                    'account' => null,
                ];

                return [$item->id => [
                    'data-receipt_printer_type' => $item->receipt_printer_type,
                    'data-default_price_group' => ! empty($item->selling_price_group_id) && array_key_exists($item->selling_price_group_id, $price_groups) ? $item->selling_price_group_id : null,
                    'data-default_payment_accounts' => json_encode($default_payment_accounts),
                    'data-default_sale_invoice_scheme_id' => $item->sale_invoice_scheme_id,
                    'data-default_invoice_scheme_id' => $item->invoice_scheme_id,
                    'data-default_invoice_layout_id' => $item->invoice_layout_id,
                ],
                ];
            })->all();

            return ['locations' => $locations, 'attributes' => $attributes];
        } else {
            return $locations;
        }
    }


    public static function SellingPriceforDropdownApi($business_id, $with_default = true)
    {
        $price_groups = SellingPriceGroup::where('business_id', $business_id)
                                    ->active()
                                    ->get();

        $dropdown = [];

        if ($with_default && auth('api')->user()->can('access_default_selling_price')) {
            $dropdown[0] = __('lang_v1.default_selling_price');
        }

        foreach ($price_groups as $price_group) {
            if (auth('api')->user()->can('selling_price_group.'.$price_group->id)) {
                $dropdown[$price_group->id] = $price_group->name;
            }
        }

        return $dropdown;
    }


}