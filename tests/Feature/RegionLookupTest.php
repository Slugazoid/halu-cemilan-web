<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegionLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_region_lookup_is_public_and_rate_limited(): void
    {
        $this->createRegions();

        $response = $this->getJson(route('regions.provinces'));

        $response->assertOk()
            ->assertJsonFragment(['code' => '32', 'name' => 'Jawa Barat'])
            ->assertHeader('X-RateLimit-Limit', '60');

        for ($attempt = 1; $attempt < 60; $attempt++) {
            $this->getJson(route('regions.provinces'))->assertOk();
        }

        $this->getJson(route('regions.provinces'))->assertTooManyRequests();
    }

    public function test_region_lookup_returns_only_children_and_places_other_village_last(): void
    {
        $this->createRegions();

        $this->getJson(route('regions.regencies', ['provinceCode' => '32']))
            ->assertOk()
            ->assertExactJson([['code' => '3201', 'name' => 'Bandung']]);

        $this->getJson(route('regions.villages', ['districtCode' => '320101']))
            ->assertOk()
            ->assertJsonPath('0.code', '3201010001')
            ->assertJsonPath('1.code', '320101LNY');
    }

    private function createRegions(): void
    {
        Province::create(['code' => '32', 'name' => 'Jawa Barat']);
        Regency::create(['code' => '3201', 'province_code' => '32', 'name' => 'Bandung']);
        District::create(['code' => '320101', 'regency_code' => '3201', 'name' => 'Coblong']);
        Village::create([
            'code' => '3201010001',
            'district_code' => '320101',
            'name' => 'Dago',
            'postal_code' => '40135',
        ]);
        Village::create([
            'code' => '320101LNY',
            'district_code' => '320101',
            'name' => 'Lainnya',
            'postal_code' => null,
        ]);
    }
}
