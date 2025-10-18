<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHcmFieldsToLocationApiCredentials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create location_api_credentials table if it doesn't exist
        if (!Schema::hasTable('location_api_credentials')) {
            Schema::create('location_api_credentials', function (Blueprint $table) {
                $table->id();
                $table->integer('business_id')->index();
                $table->integer('business_location_id')->index();
                $table->string('mall_code', 50)->comment('hcm, gallface, integra, etc.')->index();
                $table->string('api_url')->nullable();
                $table->string('api_key')->nullable();
                $table->string('client_id')->nullable();
                $table->string('client_secret')->nullable();
                $table->string('username')->nullable();
                $table->string('password')->nullable();
                $table->string('stall_no')->nullable();
                $table->string('pos_id')->nullable();
                $table->text('additional_data')->nullable();
                $table->enum('sync_type', ['auto', 'manual'])->default('manual');
                $table->integer('ping_interval')->default(5)->comment('Ping interval in minutes');
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamp('last_ping_at')->nullable();
                $table->timestamps();
            });
        }

        // Add HCM sync timestamp to transactions if not exist
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('transactions', 'hcm_synced_at')) {
                    $table->timestamp('hcm_synced_at')->nullable()->after('updated_at');
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
            });
        }

        Schema::dropIfExists('location_api_credentials');
    }
}