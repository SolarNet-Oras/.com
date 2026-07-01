<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dhcp_leases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('router_id');
            $table->uuid('customer_id')->nullable();
            $table->string('mac_address')->index();
            $table->string('ip_address')->index();
            $table->string('hostname')->nullable();
            $table->string('status'); // bound, waiting, etc.
            $table->string('server'); // DHCP server name from router
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_seen_at');
            $table->boolean('is_matched')->default(false); // Matched to customer
            $table->timestamps();
            
            $table->foreign('router_id')->references('id')->on('routers')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            
            $table->unique(['router_id', 'mac_address']);
            $table->index('is_matched');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dhcp_leases');
    }
};
