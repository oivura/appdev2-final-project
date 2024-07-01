<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate incoming request data
        $registerUserData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|min:8'
        ]);

        try {
            // Create a new user using validated data
            $user = User::create([
                'name' => $registerUserData['name'],
                'email' => $registerUserData['email'],
                'password' => Hash::make($registerUserData['password']),
            ]);

            // Return a success response
            return response()->json([
                'message' => 'User created successfully',
                'user' => $user, // Optionally return the created user data
            ], 201); // HTTP 201 Created status code
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'message' => 'Failed to create user',
                'error' => $e->getMessage(), // Optionally return the error message for debugging
            ], 500); // HTTP 500 Internal Server Error status code
        }
    }

    public function login(Request $request)
    {
        // Validate incoming request data
        $loginUserData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|min:8'
        ]);

        // Attempt to find the user by email
        $user = User::where('email', $loginUserData['email'])->first();

        // Check if user exists and verify password
        if (!$user || !Hash::check($loginUserData['password'], $user->password)) {
            // Return error response for invalid credentials
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401); // HTTP 401 Unauthorized status code
        }

        try {
            // Generate and return a new token for authenticated user
            $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'message' => 'Failed to log in',
                'error' => $e->getMessage(), // Optionally return the error message for debugging
            ], 500); // HTTP 500 Internal Server Error status code
        }
    }

    public function logout(Request $request)
    {
        // Check if a user is authenticated
        if ($request->user()) {
            // Delete all tokens associated with the authenticated user
            $request->user()->tokens()->delete();

            // Return a success response
            return response()->json([
                "message" => "Logged out successfully"
            ]);
        } else {
            // Return an error response if no user is authenticated
            return response()->json([
                "message" => "No user authenticated"
            ], 401); // HTTP 401 Unauthorized status code
        }
    }
}
