<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use App\Support\SlugGenerator;
use Illuminate\Database\Seeder;

/** Kategori, menu, grup varian, dan opsi dari mock/halu-cemilan.json. */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [];

        foreach (MockData::get('categories') as $i => $name) {
            $categories[$name] = Category::query()->firstOrCreate(
                ['name' => $name],
                ['slug' => SlugGenerator::unique(Category::class, $name), 'sort_order' => $i + 1],
            );
        }

        foreach (MockData::get('menu') as $i => $row) {
            $menu = MenuItem::query()->where('name', $row['name'])->first();

            if ($menu) {
                continue; // aman dijalankan ulang
            }

            $menu = MenuItem::query()->create([
                'category_id' => $categories[$row['cat']]->id,
                'name' => $row['name'],
                'slug' => SlugGenerator::unique(MenuItem::class, $row['name']),
                'description' => 'Placeholder deskripsi '.$row['name'].'. Ganti dengan deskripsi asli mitra.',
                'price' => $row['price'],
                'weight_grams' => $row['weight'],
                'photo_alt' => $row['name'],
                'labels' => $row['labels'],
                'is_available' => $row['available'] ?? true,
                'is_visible' => true,
                'is_featured' => in_array('Best Seller', $row['labels'], true),
                'sort_order' => $i + 1,
            ]);

            foreach ($row['options'] as $g => $group) {
                $optionGroup = $menu->optionGroups()->create([
                    'name' => $group['group'],
                    'is_required' => $group['required'],
                    'sort_order' => $g + 1,
                ]);

                foreach ($group['items'] as $o => [$name, $extraPrice, $extraWeight]) {
                    $optionGroup->options()->create([
                        'name' => $name,
                        'extra_price' => $extraPrice,
                        'extra_weight_grams' => $extraWeight,
                        'sort_order' => $o + 1,
                    ]);
                }
            }
        }
    }
}
