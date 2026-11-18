<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Domain 2.3 — Pelanggan.
 * Tabel disiapkan, tetapi fiturnya dimatikan feature flag
 * `features.customer_accounts` (checkout guest dipakai lebih dulu).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);                       // BR-13
            $table->string('phone', 15)->unique();            // 628… (BR-12)
            $table->string('password');
            $table->boolean('is_active')->default(true);      // BR-18
            $table->boolean('must_change_password')->default(false); // BR-35
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('privacy_accepted_at');         // FR-61
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('label', 20);
            $table->string('recipient_name', 50);
            $table->string('recipient_phone', 15);
            $table->string('village_code', 10);
            $table->string('postal_code', 5);
            $table->string('street_address');                 // >= 10 karakter (BR-20)
            $table->string('landmark', 150)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->foreign('village_code')->references('code')->on('villages');
            $table->index(['customer_id', 'is_primary']);
        });

        Schema::create('customer_carts', function (Blueprint $table) {
            $table->foreignId('customer_id')->primary()->constrained('customers')->cascadeOnDelete();
            $table->json('items');
            $table->timestamp('updated_at')->nullable();      // tanpa created_at (BR-10)
        });

        Schema::create('password_reset_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('code_hash');
            $table->string('channel', 10);                    // whatsapp / sms
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('created_at')->nullable();      // tanpa updated_at

            $table->index(['customer_id', 'created_at']);     // batas 5 kode/jam (BR-54)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_otps');
        Schema::dropIfExists('customer_carts');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
    }
};
