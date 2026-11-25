<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alamat terstruktur (provinsi > kabupaten/kota > kecamatan > kelurahan/desa + jalan).
     * Kolom `address` tetap ada dan diisi otomatis dari bagian-bagian ini; toko lama tetap memakai alamat teks lamanya.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->text('street')->nullable();
            $table->string('province_code', 2)->nullable();
            $table->string('regency_code', 5)->nullable();
            $table->string('district_code', 8)->nullable();
            $table->string('village_code', 13)->nullable();
            $table->string('postal_code', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['street', 'province_code', 'regency_code', 'district_code', 'village_code', 'postal_code']);
        });
    }
};
