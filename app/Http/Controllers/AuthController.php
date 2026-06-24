<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Customer;
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

    $token = $user->createToken('auth_token')->plainTextToken;

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
        'phone' => 'required|string',
        'password' => 'required|min:6'
    ]);
    $user = User::create([
        'user_name' => $request->user_name,
        'email' => $request->email,
        'phone' => $request->phone,
        'password' => Hash::make($request->password),
    ]);

    // Sync guest customer name if they register with their phone
    $customer = Customer::where('customer_phone', $request->phone)->first();
    if ($customer) {
        $customer->update([
            'customer_name' => $request->user_name
        ]);
    }

    $token = $user->createToken('auth_token')->plainTextToken;
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
