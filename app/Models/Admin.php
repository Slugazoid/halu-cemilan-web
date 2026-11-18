<?php

namespace App\Models;

use App\Enums\AdminRole;
use App\Support\PhoneNumber;
use Database\Factories\AdminFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Akun dashboard admin (guard "admin"). Login memakai nomor HP atau email.
 * Kolom role disimpan sebagai string "owner"/"staff" (lihat enum AdminRole);
 * role, is_active, dan owner_slot sengaja tidak mass-assignable.
 */
#[Fillable(['name', 'phone', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable
{
    /** @use HasFactory<AdminFactory> */
    use HasFactory;

    public function isOwner(): bool
    {
        return $this->role === AdminRole::Owner->value;
    }

    /** Normalisasi nomor HP ke 628…; input tak valid dikembalikan apa adanya agar gagal di validasi. */
    public static function normalizePhone(string $phone): string
    {
        return PhoneNumber::normalize($phone) ?? preg_replace('/[\s()-]+/', '', trim($phone));
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    protected function casts(): array
    {
        return [
            'owner_slot' => 'boolean',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'temporary_password_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
