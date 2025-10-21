<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Integra API Logs Table
        Schema::create('integra_api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('request_method', 10);
            $table->string('request_uri', 255);
            $table->longText('request_headers')->nullable();
            $table->longText('request_body')->nullable();
            $table->string('status', 50);
            $table->text('message')->nullable();
            $table->decimal('duration_ms', 10, 2)->default(0);
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
            
            $table->index('created_at');
            $table->index('status');
            $table->index('ip_address');
        });

        // Integra Transactions Table
        Schema::create('integra_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('location_code', 50);
            $table->string('terminal_id', 50)->nullable();
            $table->string('receipt_num', 100)->unique();
            $table->date('receipt_date');
            $table->date('business_date');
            $table->string('transaction_status', 20);
            $table->decimal('invoice_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->string('operational_currency', 10)->default('LKR');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->longText('raw_data')->nullable();
            $table->timestamps();
            
            $table->index('location_code');
            $table->index('receipt_date');
            $table->index('transaction_status');
        });

        // Integra Items Table
        Schema::create('integra_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->string('item_code', 100)->nullable();
            $table->string('item_name', 255)->nullable();
            $table->decimal('item_qty', 10, 2)->default(0);
            $table->decimal('item_price', 15, 2)->default(0);
            $table->string('item_category', 100)->nullable();
            $table->decimal('item_tax', 15, 2)->default(0);
            $table->string('item_tax_type', 10)->default('I');
            $table->decimal('item_net_amount', 15, 2)->default(0);
            $table->string('item_status', 20)->nullable();
            $table->decimal('item_discount', 15, 2)->default(0);
            $table->timestamps();
            
            $table->foreign('transaction_id')->references('id')->on('integra_transactions')->onDelete('cascade');
            $table->index('item_code');
        });

        // Integra Payments Table
        Schema::create('integra_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->string('payment_name', 100)->nullable();
            $table->string('currency_code', 10)->default('LKR');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->decimal('tender_amount', 15, 2)->default(0);
            $table->string('payment_status', 20)->nullable();
            $table->timestamps();
            
            $table->foreign('transaction_id')->references('id')->on('integra_transactions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('integra_payments');
        Schema::dropIfExists('integra_items');
        Schema::dropIfExists('integra_transactions');
        Schema::dropIfExists('integra_api_logs');
    }
};
