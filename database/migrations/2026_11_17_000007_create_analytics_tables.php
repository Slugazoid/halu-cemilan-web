<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Domain 2.6 — Statistik.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);                       // visit / menu_view / banner_click / order
            $table->char('visitor_id', 36);                   // ID anonim (BR-46)
            $table->char('session_id', 36)->index();
            $table->string('source', 20)->nullable();         // ig, wa, qr, google, tiktok, direct
            $table->string('subject_type', 20)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('path')->nullable();
            $table->timestamp('created_at')->nullable();      // dihapus setelah 90 hari (BR-48)

            $table->index(['type', 'created_at']);
            $table->index('created_at');
        });

        Schema::create('daily_stats', function (Blueprint $table) {
            $table->date('date')->primary();                  // zona waktu toko
            $table->unsignedInteger('visits')->default(0);
            $table->unsignedInteger('unique_visitors')->default(0);
            $table->unsignedInteger('orders')->default(0);
            $table->unsignedBigInteger('order_value')->default(0); // total estimasi (BR-47)
            $table->unsignedInteger('new_customers')->default(0);
            $table->unsignedInteger('returning_customers')->default(0);
            $table->json('by_source')->nullable();
            $table->json('by_menu')->nullable();
            $table->json('by_promo')->nullable();
            $table->json('by_banner')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_stats');
        Schema::dropIfExists('analytics_events');
    }
};
