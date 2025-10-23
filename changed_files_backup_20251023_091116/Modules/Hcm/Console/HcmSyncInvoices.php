
<?php

namespace Modules\Hcm\Console;

use Illuminate\Console\Command;
use App\Business;
use Modules\Hcm\Entities\HcmTenantConfig;
use Modules\Hcm\Utils\HcmUtil;

class HcmSyncInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcm:sync-invoices {business_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync invoices with HCM API';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $business_id = $this->argument('business_id');
        $hcmUtil = new HcmUtil();

        if ($business_id) {
            $businesses = Business::where('id', $business_id)->get();
        } else {
            // Get all businesses with active HCM configurations
            $businesses = Business::whereHas('hcmTenantConfigs', function($query) {
                $query->where('active', 1)->where('auto_sync', 1);
            })->get();
        }

        foreach ($businesses as $business) {
            try {
                $this->info("Syncing invoices for business: {$business->name}");
                
                $result = $hcmUtil->syncInvoices($business->id, 1); // System user
                
                $this->info("Synced {$result['synced_count']} invoices for business: {$business->name}");
                
            } catch (\Exception $e) {
                $this->error("Error syncing invoices for business {$business->name}: " . $e->getMessage());
            }
        }

        $this->info('HCM invoice sync completed.');
    }
}
