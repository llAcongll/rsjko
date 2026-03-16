<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $this->authorizePermission('USER_VIEW');

        return response()->json(
            User::select('id', 'username', 'role', 'permissions')->get()
        );
    }

    public function store(Request $request)
    {
        $this->authorizePermission('USER_MANAGE');

        $data = $request->validate([
            'username' => 'required|unique:users',
            'password' => 'required',
            'role' => 'required|in:ADMIN,USER',
            'permissions' => 'nullable|array'
        ]);

        User::create([
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'permissions' => $data['permissions'] ?? [],
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorizePermission('USER_MANAGE');

        $data = $request->validate([
            'username' => 'required|unique:users,username,' . $user->id,
            'role' => 'required|in:ADMIN,USER',
            'password' => 'nullable',
            'permissions' => 'nullable|array'
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy(User $user)
    {
        $this->authorizePermission('USER_MANAGE');

        $user->delete();

        return response()->json(['success' => true]);
    }

    public function show(User $user)
    {
        $this->authorizePermission('USER_VIEW');

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
            'permissions' => $user->permissions,
        ]);
    }
}





