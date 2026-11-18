<?php

namespace App\Models;

use App\Concerns\ClearsPublicCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Menu/produk. Soft delete = diarsipkan (BR-07). */
class MenuItem extends Model
{
    use ClearsPublicCache, SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'weight_grams' => 'integer',
            'labels' => 'array',
            'is_available' => 'boolean',
            'is_visible' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function optionGroups(): HasMany
    {
        return $this->hasMany(MenuOptionGroup::class)->orderBy('sort_order');
    }

    public function discounts(): MorphToMany
    {
        return $this->morphToMany(Discount::class, 'discountable');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Menu yang tampil di website (Habis tetap tampil, abu-abu — BR-05). */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    /** Menu yang bisa dimasukkan keranjang. */
    public function scopeOrderable(Builder $query): Builder
    {
        return $query->where('is_visible', true)->where('is_available', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true)->orderBy('sort_order');
    }
}
