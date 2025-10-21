
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHcmTenantConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hcm_tenant_configs', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('location_id');
            $table->string('tenant_id');
            $table->string('tenant_secret');
            $table->string('api_url')->default('https://trms-api.azurewebsites.net');
            $table->string('pos_id');
            $table->string('stall_no')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('auto_sync')->default(true);
            $table->integer('retry_attempts')->default(3);
            $table->text('additional_settings')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'location_id']);
            $table->index('tenant_id');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hcm_tenant_configs');
    }
}
