<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Domain 2.2 — Katalog.
 * Tabel: categories, menu_items, menu_option_groups, menu_options.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('slug', 60)->unique();             // BR-49
            $table->text('description')->nullable();
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 170)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');   // RESTRICT
            $table->string('name', 80);
            $table->string('slug', 60)->unique();             // BR-49
            $table->text('description')->nullable();
            $table->unsignedInteger('price');                 // Rupiah, > 0 (BR-04)
            $table->unsignedSmallInteger('weight_grams');     // BR-55
            $table->string('photo_path')->nullable();
            $table->string('photo_alt', 125)->nullable();
            $table->json('labels')->nullable();               // Best Seller, Pedas, Baru
            $table->boolean('is_available')->default(true);   // Tersedia / Habis
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_featured')->default(false);   // maks. 8 (FR-15)
            $table->text('ingredients')->nullable();
            $table->string('shelf_life', 50)->nullable();
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 170)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();                            // diarsipkan (BR-07)

            $table->index(['category_id', 'is_visible', 'sort_order']);
        });

        Schema::create('menu_option_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->string('name', 50);                       // mis. Level pedas
            $table->boolean('is_required')->default(false);   // BR-06
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('menu_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_option_group_id')->constrained('menu_option_groups')->cascadeOnDelete();
            $table->string('name', 50);                       // mis. Level 3
            $table->unsignedInteger('extra_price')->default(0);        // tidak didiskon (BR-37)
            $table->unsignedSmallInteger('extra_weight_grams')->default(0); // BR-55
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_options');
        Schema::dropIfExists('menu_option_groups');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('categories');
    }
};
