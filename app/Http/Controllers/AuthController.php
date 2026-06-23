<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class AuthController
{
    
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken($request->user_id)->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token
    ]);
}

public function me(Request $request)
{
    return $request->user();
}
public function register(Request $request)
{
    $request->validate([
        'user_name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6'
    ]);
    $user = User::create([
        'user_name' => $request->user_name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);
    $token = $user->createToken($request->user_id)->plainTextToken;
    $data = [
        'message' => 'User created successfully',
        'status' => 201,
        'user' => $user,
        'token' => $token
    ];
    return response()->json($data);
}

public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'status' => true,
        'message' => 'Logged out successfully'
    ]);
}
}
