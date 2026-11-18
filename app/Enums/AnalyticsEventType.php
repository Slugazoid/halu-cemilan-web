<?php

namespace App\Enums;

enum AnalyticsEventType: string
{
    case Visit = 'visit';
    case MenuView = 'menu_view';
    case BannerClick = 'banner_click';
    case Order = 'order';
}
