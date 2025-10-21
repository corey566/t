
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateColomboCityTables extends Migration
{
    public function up()
    {
        // Transactions table
        Schema::create('colombo_city_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('location_code');
            $table->string('terminal_id');
            $table->string('shift_no');
            $table->string('receipt_num')->unique();
            $table->date('receipt_date');
            $table->date('business_date');
            $table->time('receipt_time');
            $table->decimal('invoice_amount', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('return_amount', 15, 2)->default(0);
            $table->enum('transaction_status', ['SALES', 'RETURN']);
            $table->string('operational_currency', 10)->default('LKR');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->decimal('discount', 15, 2)->default(0);
            $table->json('raw_data')->nullable();
            $table->timestamps();
            
            $table->index('receipt_num');
            $table->index('business_date');
            $table->index('location_code');
        });

        // Items table
        Schema::create('colombo_city_items', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_num');
            $table->string('item_code');
            $table->string('item_name');
            $table->decimal('item_qty', 10, 3);
            $table->decimal('item_price', 15, 2);
            $table->string('item_category')->nullable();
            $table->decimal('item_tax', 15, 2)->default(0);
            $table->enum('item_tax_type', ['I', 'E'])->default('I');
            $table->decimal('item_net_amount', 15, 2);
            $table->string('operational_currency', 10)->default('LKR');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->enum('item_status', ['SALES', 'RETURN']);
            $table->decimal('item_discount', 15, 2)->default(0);
            $table->timestamps();
            
            $table->index('receipt_num');
            $table->index('item_code');
        });

        // Payments table
        Schema::create('colombo_city_payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_num');
            $table->string('payment_name');
            $table->string('currency_code', 10);
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->decimal('tender_amount', 15, 2);
            $table->string('operational_currency', 10)->default('LKR');
            $table->decimal('bc_exchange_rate', 10, 4)->default(1);
            $table->enum('payment_status', ['SALES', 'RETURN']);
            $table->timestamps();
            
            $table->index('receipt_num');
        });
    }

    public function down()
    {
        Schema::dropIfExists('colombo_city_payments');
        Schema::dropIfExists('colombo_city_items');
        Schema::dropIfExists('colombo_city_transactions');
    }
}
