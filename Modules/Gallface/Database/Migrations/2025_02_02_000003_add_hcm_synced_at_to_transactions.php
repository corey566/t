
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHcmSyncedAtToTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('transactions', 'hcm_synced_at')) {
                    $table->timestamp('hcm_synced_at')->nullable()->after('updated_at');
                }
                if (!Schema::hasColumn('transactions', 'gift_voucher_amount')) {
                    $table->decimal('gift_voucher_amount', 22, 4)->default(0)->after('final_total');
                }
                if (!Schema::hasColumn('transactions', 'hcm_loyalty_amount')) {
                    $table->decimal('hcm_loyalty_amount', 22, 4)->default(0)->after('gift_voucher_amount');
                }
                if (!Schema::hasColumn('transactions', 'is_gift_voucher')) {
                    $table->boolean('is_gift_voucher')->default(false)->after('type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (Schema::hasColumn('transactions', 'hcm_synced_at')) {
                    $table->dropColumn('hcm_synced_at');
                }
                if (Schema::hasColumn('transactions', 'gift_voucher_amount')) {
                    $table->dropColumn('gift_voucher_amount');
                }
                if (Schema::hasColumn('transactions', 'hcm_loyalty_amount')) {
                    $table->dropColumn('hcm_loyalty_amount');
                }
                if (Schema::hasColumn('transactions', 'is_gift_voucher')) {
                    $table->dropColumn('is_gift_voucher');
                }
            });
        }
    }
}
