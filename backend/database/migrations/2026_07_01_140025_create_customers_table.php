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
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('account_number')->unique();
            $table->string('full_name');
            $table->text('address');
            $table->json('gps_coordinates')->nullable(); // {latitude, longitude}
            $table->string('contact_number');
            $table->string('email')->nullable();
            $table->date('installation_date');
            
            // Service & Network
            $table->uuid('router_id')->nullable();
            $table->uuid('service_plan_id')->nullable();
            $table->decimal('monthly_fee', 10, 2);
            $table->string('mac_address')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('vlan')->nullable();
            
            // Status
            $table->enum('status', ['active', 'suspended', 'expired', 'pending'])->default('pending');
            
            // ONU/OLT Information (for fiber customers)
            $table->text('onu_information')->nullable();
            $table->string('olt_port')->nullable();
            
            // Assignment
            $table->uuid('technician_id')->nullable();
            
            // Additional Info
            $table->text('notes')->nullable();
            $table->json('documents')->nullable(); // Array of document URLs
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('account_number');
            $table->index('status');
            $table->index('mac_address');
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
