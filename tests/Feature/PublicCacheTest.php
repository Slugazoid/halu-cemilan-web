<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\MenuItem;
use App\Models\StoreSetting;
use App\Support\PublicCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_remember_runs_callback_once_until_flushed(): void
    {
        $calls = 0;
        $compute = function () use (&$calls) {
            return ++$calls;
        };

        $this->assertSame(1, PublicCache::remember('demo', $compute));
        $this->assertSame(1, PublicCache::remember('demo', $compute));
        $this->assertSame(1, $calls);

        PublicCache::flush();

        $this->assertSame(2, PublicCache::remember('demo', $compute));
    }

    public function test_saving_a_public_model_clears_the_cache(): void
    {
        $this->seed();

        $this->assertSame('Halu Cemilan Instan', StoreSetting::current()->name);

        StoreSetting::query()->first()->update(['name' => 'Nama Baru']);

        $this->assertSame('Nama Baru', StoreSetting::current()->name);
    }

    public function test_every_public_model_flushes_on_save_and_delete(): void
    {
        $this->seed();
        $version = PublicCache::version();

        $faq = Faq::create(['question' => 'Tes?', 'answer' => 'Ya.']);
        $this->assertGreaterThan($version, PublicCache::version(), 'create');

        $version = PublicCache::version();
        $faq->delete();
        $this->assertGreaterThan($version, PublicCache::version(), 'delete');

        $version = PublicCache::version();
        MenuItem::first()->update(['is_available' => false]);
        $this->assertGreaterThan($version, PublicCache::version(), 'toggle Habis');
    }
}
