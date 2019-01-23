<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt-refresh', ['except' => ['login', 'register']]);
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }


    public function login(Request $request)
    {
        $user = User::where('USERS_USERNAME', $request->get('USERS_USERNAME'))->first();

        if (Hash::check($request->USERS_PASSWORD, $user->USERS_PASSWORD)) {
            $token = Auth::guard('api')->login($user);

            return response()->json([
                'code' => 1,
                'token' => $token,
                'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
            ]);
        }

        return response()->json([
            'code' => 0,
            'message' => 'Credential invalid!'
        ], 401);
    }

    public function register(Request $request)
    {
        return User::create([
            'USERS_NAME' => $request->get('USERS_NAME'),
            'USERS_USERNAME' => $request->get('USERS_USERSNAME'),
            'USERS_PASSWORD' => Hash::make($request->get('USERS_PASSWORD')),
        ]);
    }

    public function logout()
    {

    }

    public function user()
    {
        $user =  Auth::guard('api')->user();

        return $user;
    }
}
