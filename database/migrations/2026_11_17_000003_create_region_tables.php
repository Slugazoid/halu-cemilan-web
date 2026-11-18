<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Domain 2.7 — Wilayah (referensi). Dibuat lebih awal karena customer_addresses
 * punya FK ke villages. Tanpa timestamps. Dipakai nanti saat fitur akun aktif.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->string('code', 2)->primary();
            $table->string('name', 100);
        });

        Schema::create('regencies', function (Blueprint $table) {
            $table->string('code', 4)->primary();
            $table->string('province_code', 2);
            $table->string('name', 100);

            $table->foreign('province_code')->references('code')->on('provinces');
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->string('code', 6)->primary();
            $table->string('regency_code', 4);
            $table->string('name', 100);

            $table->foreign('regency_code')->references('code')->on('regencies');
        });

        Schema::create('villages', function (Blueprint $table) {
            $table->string('code', 10)->primary();
            $table->string('district_code', 6)->index();
            $table->string('name', 100);
            $table->string('postal_code', 5)->nullable();

            $table->foreign('district_code')->references('code')->on('districts');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('regencies');
        Schema::dropIfExists('provinces');
    }
};
