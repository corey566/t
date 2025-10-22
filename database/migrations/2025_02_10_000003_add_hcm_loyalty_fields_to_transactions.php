
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('hcm_loyalty_amount', 22, 4)->default(0)->after('discount_amount');
            $table->enum('hcm_loyalty_type', ['fixed', 'percentage'])->default('fixed')->after('hcm_loyalty_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['hcm_loyalty_amount', 'hcm_loyalty_type']);
        });
    }
};
