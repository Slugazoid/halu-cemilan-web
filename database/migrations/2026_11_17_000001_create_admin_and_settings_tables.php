<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Domain 2.1 — Admin & Pengaturan (PRD Desain Teknis).
 * Tabel: admins, store_settings, gallery_images, testimonials, faqs,
 * activity_logs, redirects.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('phone', 15)->unique();          // format 628…
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->string('role', 10)->default('staff');    // owner / staff
            $table->boolean('owner_slot')->nullable()->unique(); // true hanya untuk Pemilik → tepat satu (BR-33)
            $table->boolean('is_active')->default(true);
            $table->boolean('must_change_password')->default(true);
            $table->timestamp('temporary_password_expires_at')->nullable(); // BR-35: berlaku 24 jam
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Selalu berisi tepat satu baris (id = 1), dibuat di bawah. Kolom inti
        // nullable karena Pemilik mengisinya lewat wizard setup (PRD 4.4); toko
        // baru bisa terbit setelah nama, nomor WA, dan ≥ 1 menu terisi (BR-32).
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->string('tagline', 150)->nullable();
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('og_image_path')->nullable();
            $table->char('primary_color', 7)->default('#27272A');
            $table->char('accent_color', 7)->default('#F2A900');
            $table->string('font', 50)->default('System');
            $table->string('theme', 30)->default('netral');
            $table->json('custom_labels')->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('maps_url')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('whatsapp_number', 15)->nullable(); // 628… (BR-29)
            $table->text('opening_hours')->nullable();        // teks bebas, info saja (BR-01)
            $table->text('shipping_info')->nullable();
            $table->longText('privacy_policy')->nullable();
            $table->longText('terms')->nullable();
            $table->string('message_greeting', 200)->nullable();
            $table->string('message_closing', 200)->nullable();
            $table->string('order_code_prefix', 3)->default('HC');
            $table->unsignedInteger('order_sequence')->default(0); // BR-22
            $table->json('social_links')->nullable();
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 170)->nullable();
            $table->json('meta_templates')->nullable();
            $table->string('gsc_verification')->nullable();
            $table->string('domain')->nullable();
            $table->boolean('is_published')->default(false); // BR-32
            $table->timestamps();
        });

        DB::table('store_settings')->insert(['id' => 1, 'created_at' => now(), 'updated_at' => now()]);

        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('alt', 125);
            $table->unsignedInteger('sort_order')->default(0); // maks. 12 foto (FR-05)
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->text('quote');
            $table->string('photo_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question', 200);
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('action', 50);
            $table->string('subject_type', 20)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->nullable();      // tanpa updated_at

            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('old_path')->unique();
            $table->string('new_path');
            $table->unsignedSmallInteger('status_code')->default(301); // BR-50
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('gallery_images');
        Schema::dropIfExists('store_settings');
        Schema::dropIfExists('admins');
    }
};
