<?php

namespace Tests\Feature;

use Database\Seeders\RegionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_region_dataset_is_complete_and_idempotent_with_other_village_per_district(): void
    {
        $this->seed(RegionSeeder::class);

        $this->assertSame(38, DB::table('provinces')->count());
        $this->assertSame(514, DB::table('regencies')->count());
        $this->assertSame(7285, DB::table('districts')->count());
        $this->assertSame(91047, DB::table('villages')->count());
        $this->assertSame(7285, DB::table('villages')->where('code', 'like', '%LNY')->count());
        $this->assertSame(
            7285,
            DB::table('villages')->where('code', 'like', '%LNY')->distinct()->count('district_code'),
        );
        foreach (DB::table('villages')->where('code', 'like', '%LNY')->get(['code', 'district_code']) as $village) {
            $this->assertSame($village->district_code.'LNY', $village->code);
            $this->assertLessThanOrEqual(10, strlen($village->code));
            $this->assertMatchesRegularExpression('/^\d{6}LNY$/D', $village->code);
        }

        $this->seed(RegionSeeder::class);

        $this->assertSame(91047, DB::table('villages')->count());
        $this->assertDatabaseHas('villages', [
            'code' => '110101LNY',
            'district_code' => '110101',
            'name' => 'Lainnya',
            'postal_code' => null,
        ]);
    }
}
