<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UserAuthController extends Controller
{
    /**
     * Show the home page.
     */
    public function showHome(): View
    {
        return view('home');
    }

    /**
     * Handle a user login request.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $request->authenticate();
            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'user' => Auth::user(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'redirect' => route('home'),
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
