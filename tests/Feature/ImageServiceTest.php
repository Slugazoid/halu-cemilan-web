<?php

namespace Tests\Feature;

use App\Jobs\ConvertImageToWebp;
use App\Models\Category;
use App\Models\MenuItem;
use App\Services\ImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ImageServiceTest extends TestCase
{
    use RefreshDatabase;

    private function menu(): MenuItem
    {
        $category = Category::query()->create(['name' => 'Camilan', 'slug' => 'camilan']);

        return $category->menuItems()->create([
            'name' => 'Basreng Pedas', 'slug' => 'basreng-pedas', 'price' => 15000, 'weight_grams' => 100,
        ]);
    }

    public function test_upload_is_converted_to_webp_and_saved_to_the_model(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $menu = $this->menu();

        app(ImageService::class)->queue($menu, 'photo_path', UploadedFile::fake()->image('foto.jpg', 2400, 1200), $menu->slug, 'menu');

        $menu->refresh();
        $this->assertMatchesRegularExpression('#^menu/basreng-pedas-[a-z0-9]{6}\.webp$#', $menu->photo_path);
        Storage::disk('public')->assertExists($menu->photo_path);
        $this->assertSame([], Storage::disk('local')->allFiles('tmp/images'));

        [$width] = getimagesizefromstring(Storage::disk('public')->get($menu->photo_path));
        $this->assertSame(ConvertImageToWebp::MAX_WIDTH, $width);
    }

    public function test_replacing_a_photo_deletes_the_old_file(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $menu = $this->menu();
        $service = app(ImageService::class);

        $service->queue($menu, 'photo_path', UploadedFile::fake()->image('a.png', 300, 300), $menu->slug, 'menu');
        $first = $menu->refresh()->photo_path;
        $service->queue($menu, 'photo_path', UploadedFile::fake()->image('b.webp', 300, 300), $menu->slug, 'menu');

        $this->assertNotSame($first, $menu->refresh()->photo_path);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($menu->photo_path);
    }

    public function test_job_is_dispatched_to_the_queue(): void
    {
        Storage::fake('local');
        Queue::fake();
        $menu = $this->menu();

        app(ImageService::class)->queue($menu, 'photo_path', UploadedFile::fake()->image('foto.jpg'), $menu->slug, 'menu');

        Queue::assertPushed(ConvertImageToWebp::class, fn (ConvertImageToWebp $job) => $job->modelId === $menu->id && $job->column === 'photo_path');
    }

    public function test_file_over_5_mb_is_rejected(): void
    {
        Queue::fake();
        $menu = $this->menu();

        $this->expectException(ValidationException::class);

        try {
            app(ImageService::class)->queue($menu, 'photo_path', UploadedFile::fake()->image('besar.jpg')->size(5121), $menu->slug, 'menu');
        } finally {
            Queue::assertNothingPushed();
        }
    }

    public function test_non_image_and_unsupported_formats_are_rejected(): void
    {
        Queue::fake();
        $menu = $this->menu();
        $service = app(ImageService::class);

        foreach ([UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'), UploadedFile::fake()->image('animasi.gif')] as $file) {
            try {
                $service->queue($menu, 'photo_path', $file, $menu->slug, 'menu');
                $this->fail('Seharusnya ditolak: '.$file->getClientOriginalName());
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }

        Queue::assertNothingPushed();
    }
}
