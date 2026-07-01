<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g., "Gold 100Mbps"
            $table->decimal('price', 10, 2); // Monthly price
            $table->text('description')->nullable();
            
            // Bandwidth limits (in Mbps)
            $table->integer('download_speed'); // Download limit in Mbps
            $table->integer('upload_speed'); // Upload limit in Mbps
            
            // Burst configuration (temporary speed boost)
            $table->integer('burst_download')->nullable(); // Burst download in Mbps
            $table->integer('burst_upload')->nullable(); // Burst upload in Mbps
            $table->integer('burst_threshold')->nullable(); // Average rate threshold in Mbps
            $table->integer('burst_time')->nullable(); // Burst time in seconds
            
            // QoS Priority (1-8, lower = higher priority)
            $table->integer('priority')->default(8);
            
            // Status
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_plans');
    }
};
