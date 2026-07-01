<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id')->nullable();
            $table->uuid('customer_id');
            $table->string('payment_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'online', 'cheque', 'credit_card', 'mobile_money'])->default('cash');
            $table->date('payment_date');
            $table->string('transaction_id')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->index(['customer_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
