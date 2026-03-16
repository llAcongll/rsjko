<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Centralized permission checking logic.
     * Prevents application crashes by ensuring user is authenticated.
     * Optionally allows admin to bypass permission check.
     */
    protected function authorizePermission($permission, $allowAdmin = false)
    {
        $user = auth()->user();
        abort_unless($user && ($user->hasPermission($permission) || ($allowAdmin && $user->role === 'ADMIN')), 403);
    }
}





