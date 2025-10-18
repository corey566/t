
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hcm_ping_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('location_id');
            $table->unsignedInteger('user_id');
            $table->string('username');
            $table->string('ip_address', 45);
            $table->string('tenant_id')->nullable();
            $table->string('pos_id')->nullable();
            $table->boolean('success')->default(false);
            $table->text('message')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamp('pinged_at');
            $table->timestamps();

            $table->index(['location_id', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hcm_ping_logs');
    }
};
