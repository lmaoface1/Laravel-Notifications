<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // POST /api/register
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('viewer'); 

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'         => $user,
            'roles'        => $user->getRoleNames(),       
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], 201);
    }

    // POST /api/login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'         => $user,
            'roles'        => $user->getRoleNames(),       
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], 200);
    }

    // POST /api/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    // GET /api/me
    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'user'        => $user,
            'roles'       => $user->getRoleNames(),        
            'permissions' => $user->getAllPermissions()->pluck('name'), 
        ], 200);
    }
}