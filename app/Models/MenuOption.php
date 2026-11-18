<?php

namespace App\Models;

use App\Concerns\ClearsPublicCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuOption extends Model
{
    use ClearsPublicCache;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'extra_price' => 'integer',
            'extra_weight_grams' => 'integer',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(MenuOptionGroup::class, 'menu_option_group_id');
    }
}
