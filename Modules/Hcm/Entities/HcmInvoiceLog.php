
<?php

namespace Modules\Hcm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HcmInvoiceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'location_id',
        'transaction_id',
        'invoice_no',
        'status',
        'request_data',
        'response_data',
        'response_message',
        'retry_count',
        'synced_at',
        'last_retry_at'
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'synced_at' => 'datetime',
        'last_retry_at' => 'datetime'
    ];

    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    public function location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id');
    }

    public function transaction()
    {
        return $this->belongsTo(\App\Transaction::class);
    }

    protected static function newFactory()
    {
        return \Modules\Hcm\Database\factories\HcmInvoiceLogFactory::new();
    }
}
