<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Domain 2.4 — Pesanan.
 *
 * Perbaikan salah ketik PRD: orders.id adalah BIGINT UNSIGNED AUTO_INCREMENT
 * (bukan VARCHAR(12)) supaya cocok dengan order_items.order_id.
 *
 * Checkout guest: customer_id selalu NULL selama fitur akun mati. Pembatasan
 * pemakaian kode promo per pelanggan memakai customer_phone (628… ternormalisasi).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();             // mis. HC-0001 (BR-22)
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_name', 50);              // snapshot
            $table->string('customer_phone', 15);             // snapshot, 628…
            $table->json('address_snapshot');                 // BR-24
            $table->string('notes', 200)->nullable();
            $table->unsignedInteger('subtotal');              // setelah diskon menu (BR-28)
            $table->foreignId('promo_code_id')->nullable()->constrained('promo_codes')->nullOnDelete();
            $table->string('promo_code', 20)->nullable();     // snapshot kode
            $table->unsignedInteger('promo_discount')->default(0);
            $table->unsignedInteger('total_estimate');
            $table->unsignedInteger('total_weight_grams');    // BR-55
            $table->string('source', 20)->nullable();         // ig, qr, google, … (FR-105)
            $table->char('idempotency_key', 36)->unique();    // BR-26
            $table->timestamps();

            $table->index('created_at');
            $table->index(['customer_id', 'created_at']);
            $table->index(['promo_code_id', 'customer_id']);
            $table->index(['promo_code_id', 'customer_phone']); // batas pakai per nomor WA
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('menu_item_id')->nullable()->constrained('menu_items')->nullOnDelete();
            $table->string('name', 80);                       // snapshot
            $table->unsignedInteger('base_price');            // harga normal saat dipesan
            $table->unsignedInteger('unit_price');            // setelah diskon
            $table->foreignId('discount_id')->nullable()->constrained('discounts')->nullOnDelete();
            $table->json('options')->nullable();              // snapshot varian
            $table->unsignedInteger('options_extra')->default(0);
            $table->unsignedTinyInteger('quantity');          // 1–99 (BR-08)
            $table->string('notes', 100)->nullable();
            $table->unsignedInteger('line_total');            // (unit_price + options_extra) × quantity
            $table->unsignedInteger('weight_grams');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
