<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Notifications\UserActivityNotification;

class UserController extends Controller
{
    // GET /api/users  (admin only)
    public function index()
    {
        return response()->json(User::with('roles')->get(), 200);
    }

    // POST /api/users  (admin only)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role'     => 'sometimes|string|exists:roles,name',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role'] ?? 'viewer');

        // 👇 Send notification when admin creates a user
        $user->notify(new UserActivityNotification('Your account has been created by an administrator.'));

        return response()->json([
            'user'  => $user,
            'roles' => $user->getRoleNames(),
        ], 201);
    }

    // GET /api/users/{id}  (admin or editor)
    public function show($id)
    {
        $user = User::with('roles')->find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user, 200);
    }

    // PUT /api/users/{id}  (admin or editor)
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:6',
            'role'     => 'sometimes|string|exists:roles,name',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        if (isset($validated['role'])) {
            $user->syncRoles($validated['role']);
            unset($validated['role']);
        }

        $user->update($validated);

        return response()->json([
            'user'  => $user,
            'roles' => $user->getRoleNames(),
        ], 200);
    }

    // DELETE /api/users/{id}  (admin only)
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // 👇 Send notification before deleting
        $user->notify(new UserActivityNotification('Your account has been deleted by an administrator.'));

        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}