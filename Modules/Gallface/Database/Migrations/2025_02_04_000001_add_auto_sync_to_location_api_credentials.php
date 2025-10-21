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
        if (!Schema::hasTable('location_api_credentials')) {
            return;
        }

        Schema::table('location_api_credentials', function (Blueprint $table) {
            if (Schema::hasColumn('location_api_credentials', 'auto_sync_enabled')) {
                return;
            }
            $table->boolean('auto_sync_enabled')->default(false)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('location_api_credentials')) {
            return;
        }
        Schema::table('location_api_credentials', function (Blueprint $table) {
            if (!Schema::hasColumn('location_api_credentials', 'auto_sync_enabled')) {
                return;
            }
            $table->dropColumn('auto_sync_enabled');
        });
    }
};