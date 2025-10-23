
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('location_api_credentials', function (Blueprint $table) {
            $table->boolean('auto_sync_enabled')->default(false)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('location_api_credentials', function (Blueprint $table) {
            $table->dropColumn('auto_sync_enabled');
        });
    }
};
