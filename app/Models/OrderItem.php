<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'unit_price' => 'integer',
            'options' => 'array',
            'options_extra' => 'integer',
            'quantity' => 'integer',
            'line_total' => 'integer',
            'weight_grams' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class)->withTrashed();
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
