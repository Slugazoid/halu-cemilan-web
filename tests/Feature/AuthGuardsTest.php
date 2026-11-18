<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthGuardsTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(array $overrides = []): Admin
    {
        return Admin::factory()->create(array_merge([
            'name' => 'Sari',
            'phone' => '6281200000009',
            'password' => 'RahasiaBanget1',
        ], $overrides));
    }

    public function test_two_separate_guards_are_configured(): void
    {
        $this->assertSame('admin', config('auth.defaults.guard'));
        $this->assertSame('admins', config('auth.guards.admin.provider'));
        $this->assertSame('customers', config('auth.guards.customer.provider'));
        $this->assertSame(Admin::class, config('auth.providers.admins.model'));
        $this->assertSame(Customer::class, config('auth.providers.customers.model'));
    }

    public function test_admin_can_log_in_with_phone_and_password(): void
    {
        $this->makeAdmin();

        $ok = Auth::guard('admin')->attempt([
            'phone' => '6281200000009', 'password' => 'RahasiaBanget1', 'is_active' => true,
        ]);

        $this->assertTrue($ok);
        $this->assertSame('Sari', Auth::guard('admin')->user()->name);
    }

    public function test_admin_login_fails_with_wrong_password(): void
    {
        $this->makeAdmin();

        $this->assertFalse(Auth::guard('admin')->attempt([
            'phone' => '6281200000009', 'password' => 'salah', 'is_active' => true,
        ]));
    }

    public function test_deactivated_admin_cannot_log_in(): void
    {
        $this->makeAdmin(['is_active' => false]);

        $this->assertFalse(Auth::guard('admin')->attempt([
            'phone' => '6281200000009', 'password' => 'RahasiaBanget1', 'is_active' => true,
        ]));
    }

    public function test_password_is_hashed_in_database(): void
    {
        $admin = $this->makeAdmin();

        $this->assertNotSame('RahasiaBanget1', $admin->getRawOriginal('password'));
    }

    public function test_admin_session_does_not_leak_into_customer_guard(): void
    {
        $admin = $this->makeAdmin();

        Auth::guard('admin')->login($admin);

        $this->assertTrue(Auth::guard('admin')->check());
        $this->assertFalse(Auth::guard('customer')->check());
    }

    public function test_customer_guard_authenticates_customers_only(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Dimas',
            'phone' => '6281234567890',
            'password' => 'Sandi12345',
        ]);

        $this->assertTrue(Auth::guard('customer')->attempt([
            'phone' => '6281234567890', 'password' => 'Sandi12345',
        ]));
        $this->assertTrue(Auth::guard('customer')->user()->is($customer));
        $this->assertFalse(Auth::guard('admin')->check());
    }
}
