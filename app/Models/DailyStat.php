<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyStat extends Model
{
    protected $primaryKey = 'date';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'order_value' => 'integer',
            'by_source' => 'array',
            'by_menu' => 'array',
            'by_promo' => 'array',
            'by_banner' => 'array',
        ];
    }
}
