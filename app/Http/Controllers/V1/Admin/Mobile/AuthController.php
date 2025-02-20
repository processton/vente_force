<?php

namespace Crater\Http\Controllers\V1\Admin\Mobile;

use Crater\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Crater\Models\User;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // $tenant = Tenancy::tenant(); // Get current tenant
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])
            ->first();

        if (!$user || !\Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $token = JWTAuth::fromUser($user);

        RateLimiter::clear($request->ip());

        return response()->json([
            'token' => $token,
            'user' => $user,
            'company' => $user->companies()->first()
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function me()
    {
        return response()->json(JWTAuth::user());
    }
}
