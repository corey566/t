
<?php

namespace Modules\Hcm\Console;

use Illuminate\Console\Command;
use App\Business;
use Modules\Hcm\Entities\HcmTenantConfig;
use Modules\Hcm\Utils\HcmUtil;

class HcmPingMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcm:ping-monitor {business_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send ping to HCM API to monitor POS status';

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
            $configs = HcmTenantConfig::where('business_id', $business_id)
                                    ->where('active', 1)
                                    ->get();
        } else {
            $configs = HcmTenantConfig::where('active', 1)->get();
        }

        foreach ($configs as $config) {
            try {
                $this->info("Sending ping for location: {$config->location->name}");
                
                $hcmUtil->sendPing($config->business_id, $config->location_id);
                
                $this->info("Ping sent successfully for location: {$config->location->name}");
                
            } catch (\Exception $e) {
                $this->error("Error sending ping for location {$config->location->name}: " . $e->getMessage());
            }
        }

        $this->info('HCM ping monitor completed.');
    }
}
