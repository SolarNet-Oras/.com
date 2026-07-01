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
        Schema::table('customers', function (Blueprint $table) {
            // service_plan_id, mac_address, ip_address are already in fillable
            // Just add foreign key constraint for service_plan_id if column doesn't have it
            if (!Schema::hasColumn('customers', 'service_plan_id')) {
                $table->uuid('service_plan_id')->nullable()->after('router_id');
            }
            $table->foreign('service_plan_id')->references('id')->on('service_plans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['service_plan_id']);
        });
    }
};
