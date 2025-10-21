
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('location_api_credentials', function (Blueprint $table) {
            $table->integer('ping_interval')->default(5)->after('sync_type')->comment('Ping interval in minutes');
            $table->timestamp('last_ping_at')->nullable()->after('last_synced_at');
        });
    }

    public function down()
    {
        Schema::table('location_api_credentials', function (Blueprint $table) {
            $table->dropColumn(['ping_interval', 'last_ping_at']);
        });
    }
};
