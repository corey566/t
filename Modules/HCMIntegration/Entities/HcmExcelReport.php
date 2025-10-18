
<?php

namespace Modules\HCMIntegration\Entities;

use Illuminate\Database\Eloquent\Model;
use App\User;

class HcmExcelReport extends Model
{
    protected $fillable = [
        'config_id',
        'report_type',
        'report_date',
        'file_path',
        'invoice_count',
        'created_by'
    ];

    protected $casts = [
        'report_date' => 'date'
    ];

    public function config()
    {
        return $this->belongsTo(HcmTenantConfig::class, 'config_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
