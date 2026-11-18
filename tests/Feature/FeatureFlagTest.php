<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FeatureFlagTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_accounts_are_off_by_default(): void
    {
        $this->assertFalse(config('features.customer_accounts'));
    }

    public function test_customer_pages_do_not_exist_while_flag_is_off(): void
    {
        $this->seed();

        foreach (['/daftar', '/masuk', '/profil', '/profil/riwayat'] as $path) {
            $this->get($path)->assertNotFound();
        }

        $this->assertNull(Route::getRoutes()->getByName('customer.login'));
    }

    public function test_customer_tables_exist_even_while_flag_is_off(): void
    {
        $this->assertTrue(Schema::hasTable('customers'));
        $this->assertTrue(Schema::hasTable('customer_addresses'));
    }
}
