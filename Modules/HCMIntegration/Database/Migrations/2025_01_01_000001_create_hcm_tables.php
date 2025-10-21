
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHcmTables extends Migration
{
    public function up()
    {
        // Tenant configurations per location
        Schema::create('hcm_tenant_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('location_id');
            $table->string('username');
            $table->string('password');
            $table->string('stall_no');
            $table->string('pos_id');
            $table->string('api_url')->default('https://trms-api.azurewebsites.net');
            $table->text('access_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_sync')->default(true);
            $table->timestamps();
            
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('business_locations')->onDelete('cascade');
            $table->unique(['business_id', 'location_id']);
        });

        // Invoice sync logs
        Schema::create('hcm_invoice_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('config_id');
            $table->unsignedInteger('transaction_id');
            $table->string('invoice_no');
            $table->string('status'); // pending, success, failed
            $table->text('request_data')->nullable();
            $table->text('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->foreign('config_id')->references('id')->on('hcm_tenant_configs')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->index(['status', 'synced_at']);
        });

        // POS ping monitor logs
        Schema::create('hcm_ping_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('config_id');
            $table->string('status'); // online, offline
            $table->text('response_data')->nullable();
            $table->timestamp('pinged_at');
            $table->timestamps();
            
            $table->foreign('config_id')->references('id')->on('hcm_tenant_configs')->onDelete('cascade');
            $table->index(['config_id', 'pinged_at']);
        });

        // Excel report generations
        Schema::create('hcm_excel_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('config_id');
            $table->string('report_type'); // day_end, month_end
            $table->date('report_date');
            $table->string('file_path');
            $table->integer('invoice_count')->default(0);
            $table->unsignedInteger('created_by');
            $table->timestamps();
            
            $table->foreign('config_id')->references('id')->on('hcm_tenant_configs')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hcm_excel_reports');
        Schema::dropIfExists('hcm_ping_logs');
        Schema::dropIfExists('hcm_invoice_logs');
        Schema::dropIfExists('hcm_tenant_configs');
    }
}
