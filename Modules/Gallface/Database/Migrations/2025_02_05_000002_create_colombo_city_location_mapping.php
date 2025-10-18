<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateColomboCityLocationMapping extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('colombo_city_location_mapping')) {
            Schema::create('colombo_city_location_mapping', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('business_id');
                $table->unsignedInteger('business_location_id');
                $table->string('colombo_location_code');
                $table->timestamps();
                
                $table->unique(['business_id', 'business_location_id'], 'colombo_loc_mapping_unique');
                $table->index('colombo_location_code');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('colombo_city_location_mapping');
    }
}
