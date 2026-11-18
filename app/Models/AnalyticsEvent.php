<?php

namespace App\Models;

use App\Enums\AnalyticsEventType;
use App\Enums\TrafficSource;
use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => AnalyticsEventType::class,
            'source' => TrafficSource::class,
        ];
    }
}
