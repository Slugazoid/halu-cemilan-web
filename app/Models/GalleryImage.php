<?php

namespace App\Models;

use App\Concerns\ClearsPublicCache;
use Illuminate\Database\Eloquent\Model;

class GalleryImage extends Model
{
    use ClearsPublicCache;

    protected $guarded = [];
}
