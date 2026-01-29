<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'username',
        'password',
        'role',
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
}
