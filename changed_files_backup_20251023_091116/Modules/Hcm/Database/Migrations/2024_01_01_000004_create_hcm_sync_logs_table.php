
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHcmSyncLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hcm_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id');
            $table->string('sync_type'); // invoice, ping, report
            $table->string('operation_type')->nullable(); // created, updated, failed
            $table->longText('data')->nullable();
            $table->longText('details')->nullable();
            $table->integer('created_by');
            $table->timestamps();

            $table->index('business_id');
            $table->index('sync_type');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hcm_sync_logs');
    }
}
