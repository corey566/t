<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnableHcmLoyaltyToBusiness extends Migration
{
    public function up()
    {
        Schema::table('business', function (Blueprint $table) {
            if (!Schema::hasColumn('business', 'enable_hcm_loyalty')) {
                $table->boolean('enable_hcm_loyalty')->default(false)->after('pos_settings');
            }
        });
    }

    public function down()
    {
        Schema::table('business', function (Blueprint $table) {
            if (Schema::hasColumn('business', 'enable_hcm_loyalty')) {
                $table->dropColumn('enable_hcm_loyalty');
            }
        });
    }
}
