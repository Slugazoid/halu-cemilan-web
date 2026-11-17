<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->string('tagline', 150)->nullable();
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('maps_url')->nullable();
            $table->string('whatsapp_number', 15)->nullable();
            $table->text('opening_hours')->nullable();
            $table->text('shipping_info')->nullable();
            $table->text('privacy_policy')->nullable();
            $table->text('terms')->nullable();
            $table->string('message_greeting', 200)->nullable();
            $table->string('message_closing', 200)->nullable();
            $table->string('order_code_prefix', 3)->default('HC');
            $table->unsignedInteger('order_sequence')->default(0);
            $table->string('primary_color', 7)->default('#27272A');
            $table->string('accent_color', 7)->default('#F2A900');
            $table->string('theme', 30)->default('netral');
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 170)->nullable();
            $table->string('gsc_verification')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        DB::table('store_settings')->insert(['id' => 1, 'created_at' => now(), 'updated_at' => now()]);

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('slug', 60)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained();
            $table->string('name', 80);
            $table->string('slug', 60)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('price');
            $table->unsignedSmallInteger('weight_grams');
            $table->text('ingredients')->nullable();
            $table->string('shelf_life', 50)->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->index(['category_id', 'is_visible', 'sort_order']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('store_settings');
    }
};
