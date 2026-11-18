<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    private const APP_TABLES = [
        // 2.1 Admin & Pengaturan
        'admins', 'store_settings', 'gallery_images', 'testimonials', 'faqs', 'activity_logs', 'redirects',
        // 2.2 Katalog
        'categories', 'menu_items', 'menu_option_groups', 'menu_options',
        // 2.3 Pelanggan
        'customers', 'customer_addresses', 'customer_carts', 'password_reset_otps',
        // 2.4 Pesanan
        'orders', 'order_items',
        // 2.5 Promo & Banner
        'discounts', 'discountables', 'promo_codes', 'banners',
        // 2.6 Statistik
        'analytics_events', 'daily_stats',
        // 2.7 Wilayah
        'provinces', 'regencies', 'districts', 'villages',
    ];

    public function test_all_27_application_tables_exist(): void
    {
        $this->assertCount(27, self::APP_TABLES);

        foreach (self::APP_TABLES as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} tidak ada");
        }
    }

    public function test_laravel_support_tables_exist_and_default_users_table_is_gone(): void
    {
        foreach (['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} tidak ada");
        }

        $this->assertFalse(Schema::hasTable('users'));
        $this->assertFalse(Schema::hasTable('password_reset_tokens'));
    }

    public function test_orders_id_is_integer_and_matches_order_items_foreign_key(): void
    {
        $orderId = collect(Schema::getColumns('orders'))->firstWhere('name', 'id');
        $this->assertStringContainsString('int', strtolower($orderId['type']));

        $fk = collect(Schema::getForeignKeys('order_items'))->firstWhere('foreign_table', 'orders');
        $this->assertNotNull($fk, 'order_items.order_id harus punya FK ke orders');
        $this->assertSame(['order_id'], $fk['columns']);
    }

    public function test_guest_checkout_columns_exist(): void
    {
        $this->assertTrue(Schema::hasColumns('orders', [
            'code', 'customer_id', 'customer_name', 'customer_phone', 'address_snapshot',
            'subtotal', 'promo_code_id', 'promo_code', 'promo_discount', 'total_estimate',
            'total_weight_grams', 'source', 'idempotency_key',
        ]));

        $customerId = collect(Schema::getColumns('orders'))->firstWhere('name', 'customer_id');
        $this->assertTrue($customerId['nullable'], 'customer_id harus nullable untuk checkout guest');
    }

    public function test_store_settings_has_theme_and_order_sequence_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('store_settings', [
            'primary_color', 'accent_color', 'font', 'theme', 'whatsapp_number',
            'order_code_prefix', 'order_sequence', 'is_published',
        ]));
    }

    public function test_catalog_uses_soft_deletes_and_unique_slugs(): void
    {
        $this->assertTrue(Schema::hasColumn('menu_items', 'deleted_at'));
        $this->assertTrue(Schema::hasColumn('categories', 'deleted_at'));

        foreach (['menu_items', 'categories'] as $table) {
            $unique = collect(Schema::getIndexes($table))
                ->contains(fn ($i) => $i['unique'] && $i['columns'] === ['slug']);
            $this->assertTrue($unique, "{$table}.slug harus UNIQUE");
        }
    }
}
