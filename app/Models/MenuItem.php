<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'category_id', 'name', 'slug', 'description', 'price', 'weight_grams', 'ingredients', 'shelf_life',
    'is_available', 'is_visible', 'is_featured', 'sort_order',
])]
class MenuItem extends Model
{
    use SoftDeletes;

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @param Builder<MenuItem> $query */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('is_visible', true);
    }

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'is_visible' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }
}
