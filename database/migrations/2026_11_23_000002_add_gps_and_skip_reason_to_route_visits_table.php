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
        Schema::table('route_visits', function (Blueprint $table) {
            $table->text('skip_reason')->nullable()->after('failure_reason');
            $table->unsignedInteger('gps_accuracy_m')->nullable()->after('simulated_longitude');
            $table->unsignedInteger('gps_distance_m')->nullable()->after('gps_accuracy_m');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('route_visits', function (Blueprint $table) {
            $table->dropColumn(['skip_reason', 'gps_accuracy_m', 'gps_distance_m']);
        });
    }
};
