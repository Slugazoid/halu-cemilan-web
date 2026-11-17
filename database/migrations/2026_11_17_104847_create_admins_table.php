<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('phone', 15)->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->string('role', 10)->default('staff');
            $table->boolean('owner_slot')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('must_change_password')->default(true);
            $table->timestamp('temporary_password_expires_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

        Schema::create('admin_password_resets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins');
            $table->foreignId('reset_by')->constrained('admins');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_password_resets');
        Schema::dropIfExists('admins');
    }
};
