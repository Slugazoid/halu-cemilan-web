<?php

namespace App\Models;

use App\Concerns\ClearsPublicCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use ClearsPublicCache;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean'];
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true)->orderBy('sort_order');
    }
}
