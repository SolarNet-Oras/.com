<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ticket_id');
            $table->uuid('user_id')->nullable();
            $table->uuid('customer_id')->nullable();
            $table->text('comment');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_comments');
    }
};
