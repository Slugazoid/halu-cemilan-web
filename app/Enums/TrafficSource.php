<?php

namespace App\Enums;

enum TrafficSource: string
{
    case Instagram = 'ig';
    case WhatsApp = 'wa';
    case Qr = 'qr';
    case Google = 'google';
    case TikTok = 'tiktok';
    case Direct = 'direct';
}
