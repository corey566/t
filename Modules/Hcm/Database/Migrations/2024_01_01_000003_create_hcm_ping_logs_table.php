
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHcmPingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hcm_ping_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('location_id');
            $table->enum('status', ['online', 'offline'])->default('offline');
            $table->text('response_data')->nullable();
            $table->string('response_message')->nullable();
            $table->timestamp('last_ping_at');
            $table->timestamps();

            $table->index(['business_id', 'location_id']);
            $table->index('status');
            $table->index('last_ping_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hcm_ping_logs');
    }
}
