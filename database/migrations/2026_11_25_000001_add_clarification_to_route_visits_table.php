<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permintaan klarifikasi admin atas hasil check-in, dan jawaban sales.
     * Admin tidak mengubah hasil check-in; ia hanya meminta penjelasan, sales yang memperbaiki/menjawab.
     */
    public function up(): void
    {
        Schema::table('route_visits', function (Blueprint $table) {
            $table->text('clarification_note')->nullable();
            $table->timestamp('clarification_requested_at')->nullable();
            $table->foreignId('clarification_requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('clarification_response')->nullable();
            $table->timestamp('clarification_answered_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('route_visits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('clarification_requested_by');
            $table->dropColumn([
                'clarification_note',
                'clarification_requested_at',
                'clarification_response',
                'clarification_answered_at',
            ]);
        });
    }
};
