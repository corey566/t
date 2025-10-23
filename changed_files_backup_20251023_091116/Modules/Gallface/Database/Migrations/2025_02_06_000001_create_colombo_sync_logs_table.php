
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('colombo_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('request_method', 10);
            $table->string('endpoint', 255);
            $table->longText('request_data')->nullable();
            $table->longText('response_data')->nullable();
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->decimal('duration_ms', 10, 2)->default(0);
            $table->timestamps();
            
            $table->index('created_at');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('colombo_sync_logs');
    }
};
