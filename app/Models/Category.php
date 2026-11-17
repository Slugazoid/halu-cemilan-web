<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'slug', 'description', 'sort_order'])]
class Category extends Model
{
    use SoftDeletes;

    /** @return HasMany<MenuItem, $this> */
    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }
}
