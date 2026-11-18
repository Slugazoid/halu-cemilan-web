<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/** FAQ dan testimoni placeholder dari mock. */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (MockData::get('faqs') as $i => [$question, $answer]) {
            Faq::query()->firstOrCreate(
                ['question' => $question],
                ['answer' => $answer, 'sort_order' => $i + 1],
            );
        }

        foreach (MockData::get('testimonials') as $i => [$name, $quote]) {
            Testimonial::query()->firstOrCreate(
                ['name' => $name],
                ['quote' => $quote, 'sort_order' => $i + 1],
            );
        }
    }
}
