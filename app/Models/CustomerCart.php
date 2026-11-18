<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerCart extends Model
{
    public const CREATED_AT = null;

    protected $primaryKey = 'customer_id';

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['items' => 'array'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
