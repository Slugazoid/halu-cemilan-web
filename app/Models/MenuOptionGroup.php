<?php

namespace App\Models;

use App\Concerns\ClearsPublicCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuOptionGroup extends Model
{
    use ClearsPublicCache;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_required' => 'boolean'];
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(MenuOption::class)->orderBy('sort_order');
    }
}
