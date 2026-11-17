<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name', 'tagline', 'description', 'address', 'city', 'maps_url', 'whatsapp_number',
    'opening_hours', 'shipping_info', 'privacy_policy', 'terms', 'message_greeting', 'message_closing',
    'order_code_prefix', 'primary_color', 'accent_color', 'theme', 'meta_title', 'meta_description', 'gsc_verification',
])]
class StoreSetting extends Model
{
    public const int SINGLETON_ID = 1;

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }
}
