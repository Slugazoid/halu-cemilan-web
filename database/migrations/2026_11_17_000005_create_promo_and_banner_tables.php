<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Domain 2.5 — Promo & Banner. Dibuat sebelum orders karena orders dan
 * order_items punya FK ke promo_codes / discounts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('type', 10);                       // percent / fixed
            $table->unsignedInteger('value');                 // persen 1–90 atau Rp (BR-36)
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();         // null = tanpa batas akhir
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at']);
        });

        // Pivot polimorfik: diskon → menu_items atau categories.
        Schema::create('discountables', function (Blueprint $table) {
            $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
            $table->string('discountable_type', 20);          // alias morph: menu / category
            $table->unsignedBigInteger('discountable_id');

            $table->primary(['discount_id', 'discountable_type', 'discountable_id']);
            $table->index(['discountable_type', 'discountable_id']);
        });

        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();             // huruf besar (BR-42)
            $table->string('type', 10);                       // percent / fixed
            $table->unsignedInteger('value');
            $table->unsignedInteger('max_discount')->nullable(); // wajib bila percent
            $table->unsignedInteger('min_spend')->nullable();    // BR-41
            $table->unsignedInteger('quota')->nullable();        // null = tanpa batas
            $table->unsignedInteger('used_count')->default(0);   // BR-43
            $table->unsignedTinyInteger('per_customer_limit')->default(1);
            $table->boolean('new_customers_only')->default(false); // BR-44 (ditunda, flag akun)
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');                     // rasio 2:1, <= 2 MB (FR-96)
            $table->string('alt_text', 125);
            $table->string('title', 80);
            $table->string('link_type', 20);                  // none / menu / category / promo_page / promo_code
            $table->unsignedBigInteger('target_id')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);      // maks. 5 aktif (FR-92)
            $table->timestamps();

            $table->index(['link_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
        Schema::dropIfExists('promo_codes');
        Schema::dropIfExists('discountables');
        Schema::dropIfExists('discounts');
    }
};
