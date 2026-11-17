<?php

namespace App\Models;

use Database\Factories\AdminFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['name', 'phone', 'email', 'password'])]
#[Hidden(['password'])]
class Admin extends Authenticatable
{
    /** @use HasFactory<AdminFactory> */
    use HasFactory;

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public static function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[\s()-]+/', '', trim($phone));

        return str_starts_with($phone, '0') ? '62'.substr($phone, 1) : ltrim($phone, '+');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'temporary_password_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
