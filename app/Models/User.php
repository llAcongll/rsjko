<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'username',
        'password',
        'role',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    protected $hidden = [
        'password',
    ];

    /* =========================
       ROLE HELPERS
    ========================== */

    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function isUser(): bool
    {
        return $this->role === 'USER';
    }

    public function hasPermission($permission): bool
    {
        // Admin selalu punya izin
        if ($this->isAdmin())
            return true;

        if (empty($this->permissions))
            return false;

        return in_array($permission, $this->permissions);
    }
}
