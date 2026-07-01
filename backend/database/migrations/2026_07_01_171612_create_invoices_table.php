<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_number')->unique();
            $table->uuid('customer_id');
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('billing_period_start');
            $table->date('billing_period_end');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->enum('status', ['draft', 'sent', 'paid', 'partial', 'overdue', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->index(['customer_id', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
