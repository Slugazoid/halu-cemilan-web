<?php

namespace App\Models;

use App\Concerns\ClearsPublicCache;
use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Discount extends Model
{
    use ClearsPublicCache;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => DiscountType::class,
            'value' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function menuItems(): MorphToMany
    {
        return $this->morphedByMany(MenuItem::class, 'discountable');
    }

    public function categories(): MorphToMany
    {
        return $this->morphedByMany(Category::class, 'discountable');
    }
}
