<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalDataToLocationApiCredentials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('location_api_credentials')) {
            return;
        }

        Schema::table('location_api_credentials', function (Blueprint $table) {
            if (Schema::hasColumn('location_api_credentials', 'property_code')) {
                return;
            }
            if (!Schema::hasColumn('location_api_credentials', 'additional_data')) {
                $table->text('additional_data')->nullable()->after('pos_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('location_api_credentials')) {
            Schema::table('location_api_credentials', function (Blueprint $table) {
                if (Schema::hasColumn('location_api_credentials', 'additional_data')) {
                    $table->dropColumn('additional_data');
                }
            });
        }
    }
}