<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CustomerAddressTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['features.customer_accounts' => true]);
        Route::middleware('web')->group(base_path('routes/customer.php'));
        Route::getRoutes()->refreshNameLookups();

        Province::create(['code' => '32', 'name' => 'Jawa Barat']);
        Province::create(['code' => '31', 'name' => 'DKI Jakarta']);
        Regency::create(['code' => '3201', 'province_code' => '32', 'name' => 'Bandung']);
        Regency::create(['code' => '3101', 'province_code' => '31', 'name' => 'Jakarta Selatan']);
        District::create(['code' => '320101', 'regency_code' => '3201', 'name' => 'Coblong']);
        District::create(['code' => '310101', 'regency_code' => '3101', 'name' => 'Kebayoran Baru']);
        Village::create([
            'code' => '3201010001',
            'district_code' => '320101',
            'name' => 'Dago',
            'postal_code' => '40135',
        ]);
        Village::create([
            'code' => '3201010002',
            'district_code' => '320101',
            'name' => 'Cipaganti',
            'postal_code' => null,
        ]);
        Village::create([
            'code' => '320101LNY',
            'district_code' => '320101',
            'name' => 'Lainnya',
            'postal_code' => null,
        ]);
        Village::create([
            'code' => '3101010001',
            'district_code' => '310101',
            'name' => 'Gunung',
            'postal_code' => '12120',
        ]);

        $this->customer = Customer::create([
            'name' => 'Rafi',
            'phone' => '6281234567890',
            'password' => 'password',
            'privacy_accepted_at' => now(),
        ]);
        $this->actingAs($this->customer, 'customer');
    }

    public function test_first_address_is_primary_and_recipient_phone_is_normalized(): void
    {
        $this->post(route('customer.addresses.store'), $this->payload([
            'recipient_phone' => '0812-3456-7890',
        ]))->assertRedirect(route('customer.addresses.index'));

        $address = CustomerAddress::firstOrFail();
        $this->assertSame('6281234567890', $address->recipient_phone);
        $this->assertSame('40135', $address->postal_code);
        $this->assertTrue($address->is_primary);
    }

    public function test_known_postal_code_can_be_corrected(): void
    {
        $this->post(route('customer.addresses.store'), $this->payload([
            'postal_code' => '40136',
        ]))->assertRedirect(route('customer.addresses.index'));

        $this->assertDatabaseHas('customer_addresses', ['postal_code' => '40136']);
    }

    public function test_map_coordinates_are_optional_but_saved_when_provided(): void
    {
        $this->post(route('customer.addresses.store'), $this->payload([
            'latitude' => '-6.8900000',
            'longitude' => '107.6100000',
        ]))->assertRedirect(route('customer.addresses.index'));

        $this->assertDatabaseHas('customer_addresses', [
            'latitude' => '-6.8900000',
            'longitude' => '107.6100000',
        ]);
    }

    public function test_plus_62_and_62_recipient_numbers_are_normalized(): void
    {
        foreach (['+62 812 3456 7890', '6281234567890'] as $index => $phone) {
            $this->post(route('customer.addresses.store'), $this->payload([
                'label' => $index === 0 ? 'Rumah' : 'Kantor',
                'recipient_phone' => $phone,
            ]))->assertRedirect(route('customer.addresses.index'));
        }

        $this->assertSame(
            ['6281234567890', '6281234567890'],
            CustomerAddress::query()->orderBy('id')->pluck('recipient_phone')->all(),
        );
    }

    public function test_manual_postal_code_is_required_when_region_data_has_no_postal_code(): void
    {
        $this->from(route('customer.addresses.create'))
            ->post(route('customer.addresses.store'), $this->payload([
                'village_code' => '3201010002',
                'postal_code' => '',
            ]))
            ->assertSessionHasErrors('postal_code');

        $this->post(route('customer.addresses.store'), $this->payload([
            'village_code' => '3201010002',
            'postal_code' => '40136',
        ]))->assertRedirect(route('customer.addresses.index'));

        $this->assertDatabaseHas('customer_addresses', ['village_code' => '3201010002', 'postal_code' => '40136']);
    }

    public function test_other_village_requires_manual_name_and_postal_code_and_stores_the_name(): void
    {
        $this->from(route('customer.addresses.create'))
            ->post(route('customer.addresses.store'), $this->payload([
                'village_code' => '320101LNY',
                'manual_village_name' => '',
                'postal_code' => '',
            ]))
            ->assertSessionHasErrors(['manual_village_name', 'postal_code']);

        $this->post(route('customer.addresses.store'), $this->payload([
            'village_code' => '320101LNY',
            'manual_village_name' => 'Sukaluyu',
            'postal_code' => '40132',
        ]))->assertRedirect(route('customer.addresses.index'));

        $address = CustomerAddress::firstOrFail();
        $this->assertSame('40132', $address->postal_code);
        $this->assertStringContainsString('Kelurahan manual: Sukaluyu', $address->street_address);
    }

    public function test_server_rejects_a_village_outside_the_selected_hierarchy(): void
    {
        $this->from(route('customer.addresses.create'))
            ->post(route('customer.addresses.store'), $this->payload([
                'province_code' => '31',
                'regency_code' => '3101',
                'district_code' => '310101',
                'village_code' => '3201010001',
            ]))
            ->assertSessionHasErrors('village_code');

        $this->assertDatabaseCount('customer_addresses', 0);
    }

    public function test_customer_cannot_store_more_than_five_addresses(): void
    {
        for ($index = 0; $index < 5; $index++) {
            $this->customer->addresses()->create($this->storedAddress($index === 0));
        }

        $this->from(route('customer.addresses.create'))
            ->post(route('customer.addresses.store'), $this->payload())
            ->assertSessionHasErrors('addresses');

        $this->assertDatabaseCount('customer_addresses', 5);
    }

    public function test_customer_can_update_owned_address_without_changing_primary_status(): void
    {
        $address = $this->customer->addresses()->create($this->storedAddress(true));

        $this->put(route('customer.addresses.update', $address), $this->payload([
            'label' => 'Kantor',
            'recipient_phone' => '+62 812 3456 7890',
            'postal_code' => '40136',
        ]))->assertRedirect(route('customer.addresses.index'));

        $this->assertDatabaseHas('customer_addresses', [
            'id' => $address->id,
            'label' => 'Kantor',
            'recipient_phone' => '6281234567890',
            'postal_code' => '40136',
            'is_primary' => true,
        ]);
    }

    public function test_customer_cannot_edit_another_customers_address(): void
    {
        $otherCustomer = Customer::create([
            'name' => 'Dimas',
            'phone' => '6281234567891',
            'password' => 'password',
            'privacy_accepted_at' => now(),
        ]);
        $address = $otherCustomer->addresses()->create($this->storedAddress(true));

        $this->put(route('customer.addresses.update', $address), $this->payload())
            ->assertNotFound();

        $this->assertDatabaseHas('customer_addresses', [
            'id' => $address->id,
            'customer_id' => $otherCustomer->id,
            'recipient_name' => 'Rafi',
        ]);
    }

    public function test_address_routes_require_customer_authentication(): void
    {
        auth('customer')->logout();

        $this->getJson(route('customer.addresses.index'))->assertUnauthorized();
    }

    public function test_primary_address_cannot_be_deleted_until_another_is_primary(): void
    {
        $primary = $this->customer->addresses()->create($this->storedAddress(true));
        $alternate = $this->customer->addresses()->create($this->storedAddress(false));

        $this->from(route('customer.addresses.index'))
            ->delete(route('customer.addresses.destroy', $primary))
            ->assertSessionHasErrors('address');
        $this->assertDatabaseHas('customer_addresses', ['id' => $primary->id, 'is_primary' => true]);

        $this->patch(route('customer.addresses.primary', $alternate))
            ->assertRedirect(route('customer.addresses.index'));
        $this->delete(route('customer.addresses.destroy', $primary))
            ->assertRedirect(route('customer.addresses.index'));

        $this->assertDatabaseMissing('customer_addresses', ['id' => $primary->id]);
        $this->assertDatabaseHas('customer_addresses', ['id' => $alternate->id, 'is_primary' => true]);
        $this->assertSame(1, $this->customer->addresses()->where('is_primary', true)->count());
    }

    public function test_form_displays_cascading_searchable_region_controls(): void
    {
        $this->get(route('customer.addresses.create'))
            ->assertOk()
            ->assertSee('Cari provinsi')
            ->assertSee('Cari kota/kabupaten')
            ->assertSee('postal_code');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'label' => 'Rumah',
            'recipient_name' => 'Rafi',
            'recipient_phone' => '081234567890',
            'province_code' => '32',
            'regency_code' => '3201',
            'district_code' => '320101',
            'village_code' => '3201010001',
            'postal_code' => '',
            'street_address' => 'Jalan Melati nomor sepuluh',
            'landmark' => 'Pagar hijau',
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function storedAddress(bool $primary): array
    {
        return [
            'label' => $primary ? 'Rumah' : 'Kantor',
            'recipient_name' => 'Rafi',
            'recipient_phone' => '6281234567890',
            'village_code' => '3201010001',
            'postal_code' => '40135',
            'street_address' => 'Jalan Melati nomor sepuluh',
            'landmark' => null,
            'is_primary' => $primary,
        ];
    }
}
