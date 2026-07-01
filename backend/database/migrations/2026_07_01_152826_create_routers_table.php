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
        Schema::create('routers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('host'); // IP address or hostname
            $table->integer('port')->default(8728); // RouterOS API port
            $table->string('username');
            $table->text('password'); // Will be encrypted
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->string('dhcp_pool_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('connection_status', ['online', 'offline', 'unknown'])->default('unknown');
            $table->string('routeros_version')->nullable();
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('connection_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routers');
    }
};
