<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => DiscountType::class,
            'value' => 'integer',
            'max_discount' => 'integer',
            'min_spend' => 'integer',
            'quota' => 'integer',
            'used_count' => 'integer',
            'per_customer_limit' => 'integer',
            'new_customers_only' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /** Kode selalu disimpan huruf besar (BR-42). */
    protected function code(): Attribute
    {
        return Attribute::make(set: fn (string $value) => strtoupper(trim($value)));
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
