<?php

namespace App\Models;

use App\Concerns\ClearsPublicCache;
use App\Enums\BannerLinkType;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use ClearsPublicCache;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'link_type' => BannerLinkType::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
