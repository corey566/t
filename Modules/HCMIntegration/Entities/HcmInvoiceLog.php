
<?php

namespace Modules\HCMIntegration\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Transaction;

class HcmInvoiceLog extends Model
{
    protected $fillable = [
        'config_id',
        'transaction_id',
        'invoice_no',
        'status',
        'request_data',
        'response_data',
        'error_message',
        'retry_count',
        'synced_at'
    ];

    protected $casts = [
        'synced_at' => 'datetime'
    ];

    public function config()
    {
        return $this->belongsTo(HcmTenantConfig::class, 'config_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }
}
