<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // GET /api/users  (admin only - enforced in routes)
    public function index()
    {
        return response()->json(User::with('roles')->get(), 200); 
    }

    // POST /api/users  (admin only - enforced in routes)
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

        return response()->json([
            'user'  => $user,
            'roles' => $user->getRoleNames(),
        ], 201);
    }

    // GET /api/users/{id}  (admin or editor - enforced in routes)
    public function show($id)
    {
        $user = User::with('roles')->find($id); 
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user, 200);
    }

    // PUT /api/users/{id}  (admin or editor - enforced in routes)
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

    // DELETE /api/users/{id}  (admin only - enforced in routes)
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}