<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class AuthController extends Controller
{
    public function login(Request $request) // data berupa json akan di kirik ke $request ini
    {
        try {
            if(!Auth::guard('web')->attempt($request->only('email','password'))){
                return response()->json([
                    'message' => 'Unauthorized',
                    'data' => 'null'
                ], 401); //gagal login
            }
            
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                    'message' => 'Login berhasil',
                    'data' => [
                        'token' => $token,
                        'user' => $user
                    ]
                ], 200); //berhasil login
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi_kesalahan',
                'error' => $e->getMessage()
            ], 500); //jika terjadi kesalahan
        }
    } 
}
