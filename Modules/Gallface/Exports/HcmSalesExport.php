
<?php

namespace Modules\Gallface\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HcmSalesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $businessId;
    protected $locationId;
    protected $startDate;
    protected $endDate;
    
    public function __construct($businessId, $locationId, $startDate, $endDate)
    {
        $this->businessId = $businessId;
        $this->locationId = $locationId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
    
    public function collection()
    {
        // This should query your actual sales table
        // Placeholder query - adjust based on your database schema
        return DB::table('transactions')
            ->where('business_id', $this->businessId)
            ->where('location_id', $this->locationId)
            ->whereBetween('transaction_date', [$this->startDate, $this->endDate])
            ->get();
    }
    
    public function headings(): array
    {
        return [
            'Invoice No',
            'Transaction Date',
            'Customer Mobile',
            'Total Amount',
            'Tax Amount',
            'Discount Amount',
            'Payment Method',
            'Gift Voucher Amount',
            'HCM Loyalty Amount',
            'Status'
        ];
    }
    
    public function map($sale): array
    {
        return [
            $sale->invoice_no ?? '',
            $sale->transaction_date ?? '',
            $sale->customer_mobile ?? '',
            $sale->final_total ?? 0,
            $sale->tax_amount ?? 0,
            $sale->discount_amount ?? 0,
            $sale->payment_method ?? '',
            $sale->gift_voucher_amount ?? 0,
            $sale->hcm_loyalty_amount ?? 0,
            $sale->payment_status ?? ''
        ];
    }
}
