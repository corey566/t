
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('location_api_credentials', function (Blueprint $table) {
            if (!Schema::hasColumn('location_api_credentials', 'ping_interval')) {
                $table->integer('ping_interval')->default(5)->after('sync_type')->comment('Ping interval in minutes');
            }
            if (!Schema::hasColumn('location_api_credentials', 'last_ping_at')) {
                $table->timestamp('last_ping_at')->nullable()->after('last_synced_at');
            }
        });
    }

    public function down()
    {
        Schema::table('location_api_credentials', function (Blueprint $table) {
            if (Schema::hasColumn('location_api_credentials', 'ping_interval')) {
                $table->dropColumn('ping_interval');
            }
            if (Schema::hasColumn('location_api_credentials', 'last_ping_at')) {
                $table->dropColumn('last_ping_at');
            }
        });
    }
};
