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
        Schema::create('route_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_route_id')->constrained('sales_routes')->onDelete('cascade');
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->unsignedInteger('sequence');
            $table->string('result')->nullable(); // successful, store_closed, failed
            $table->dateTime('checked_in_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('failure_reason')->nullable();
            $table->decimal('simulated_latitude', 10, 7)->nullable();
            $table->decimal('simulated_longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->unique(['sales_route_id', 'sequence']);
            $table->unique(['sales_route_id', 'store_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_visits');
    }
};
