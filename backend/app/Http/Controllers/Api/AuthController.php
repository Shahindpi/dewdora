<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Support\ApiResponse;
use App\Http\Requests\Auth\LoginRequest;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            return ApiResponse::error(
                'Invalid credentials.',
                401
            );
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->status) {
            Auth::logout();

            return ApiResponse::error(
                'Your account is inactive.',
                403
            );
        }

        // Remove previous tokens (optional but recommended)
        $user->tokens()->delete();

        $token = $user->createToken('dewdora-admin')->plainTextToken;

        return ApiResponse::success(
            [
                'token' => $token,
                'user'  => new UserResource(
                    $user->load('role')
                ),
            ],
            'Login successful.'
        );
    }

    /**
     * Authenticated user.
     */
    public function me(): JsonResponse
    {
        $user = auth()->user()->load('role');

        return ApiResponse::success(
            UserResource::make($user),
            'Authenticated user retrieved successfully.'
        );
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}