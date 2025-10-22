
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHcmLoyaltyFieldsToBusiness extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business', function (Blueprint $table) {
            $table->boolean('enable_hcm_loyalty')->default(0)->after('enable_rp');
            $table->text('hcm_loyalty_locations')->nullable()->after('enable_hcm_loyalty');
            $table->boolean('hcm_loyalty_independent_discount')->default(1)->after('hcm_loyalty_locations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business', function (Blueprint $table) {
            $table->dropColumn(['enable_hcm_loyalty', 'hcm_loyalty_locations', 'hcm_loyalty_independent_discount']);
        });
    }
}
