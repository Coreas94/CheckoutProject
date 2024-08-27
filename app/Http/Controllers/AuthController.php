<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => ['login']]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        \Log::info('Credentials: ', $credentials);

        $token = Auth::guard('api')->attempt($credentials);
        if (!$token) {
            $error = "Wrong password: " . $request->username;
            return response()->json([
                'meta' => [
                    'success' => false,
                    'errors' => [$error]
                ]
            ], 401);
        }

        $user = Auth::guard('api')->user();
        return response()->json([
            'meta' => [
                'success' => true,
                'errors' => []
            ],
            'data' => [
                'token' => $token,
                'minutes_to_expire' => 1440
            ]
        ], 200);
    }
}
