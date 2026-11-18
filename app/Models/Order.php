<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pesanan (tanpa status; BR-23). Semua nilai adalah snapshot saat pesan dibuat (BR-24).
 * Pada checkout guest, customer_id = null dan customer_phone (628…) jadi identitas.
 */
class Order extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'address_snapshot' => 'array',
            'subtotal' => 'integer',
            'promo_discount' => 'integer',
            'total_estimate' => 'integer',
            'total_weight_grams' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
