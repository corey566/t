
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHcmInvoiceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hcm_invoice_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('location_id');
            $table->integer('transaction_id');
            $table->string('invoice_no');
            $table->enum('status', ['pending', 'success', 'failed', 'retrying'])->default('pending');
            $table->text('request_data')->nullable();
            $table->text('response_data')->nullable();
            $table->string('response_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('last_retry_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'location_id']);
            $table->index('transaction_id');
            $table->index('invoice_no');
            $table->index('status');
            $table->index('synced_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hcm_invoice_logs');
    }
}
