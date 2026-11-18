<?php

namespace App\Enums;

enum BannerLinkType: string
{
    case None = 'none';
    case Menu = 'menu';
    case Category = 'category';
    case PromoPage = 'promo_page';
    case PromoCode = 'promo_code';
}
