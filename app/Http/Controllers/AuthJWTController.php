<?php

namespace App\Http\Controllers;

// use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthJWTController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'refresh']]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bad Request: Email or Password Empty',
            ], 400);
        }
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = Auth::guard('api')->attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not create token',
            ], 500);
        }
        ;
        $user = auth('api')->user();
        //$user = Auth::user();
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);

    }

    // public function register(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|max:255|unique:users',
    //         'password' => 'required|string|min:6',
    //     ]);

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //     ]);

    //     $token = Auth::login($user);
    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'User created successfully',
    //         'user' => $user,
    //         'authorisation' => [
    //             'token' => $token,
    //             'type' => 'bearer',
    //         ]
    //     ]);
    // }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        //Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        $token = JWTAuth::refresh();
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ], 200);
    }

}