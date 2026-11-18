<?php

namespace App\Models;

use App\Concerns\ClearsPublicCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use ClearsPublicCache, SoftDeletes;

    protected $guarded = [];

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    public function discounts(): MorphToMany
    {
        return $this->morphToMany(Discount::class, 'discountable');
    }

    /** Kategori tanpa menu tampil otomatis disembunyikan (BR-05). */
    public function scopeWithVisibleMenus(Builder $query): Builder
    {
        return $query->whereHas('menuItems', fn (Builder $q) => $q->where('is_visible', true))
            ->orderBy('sort_order');
    }
}
