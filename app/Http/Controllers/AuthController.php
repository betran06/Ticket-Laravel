<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource; //mengatur bentuk output json agar rapih dan aman
use App\Http\Requests\RegisterStoreRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;  // ← kalau kamu pakai Hash::make()
use App\Models\User;

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
                        'user' => new UserResource($user)
                    ]
                ], 200); //berhasil login
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi_kesalahan',
                'error' => $e->getMessage()
            ], 500); //jika terjadi kesalahan
        }
    }

    public function me()
    {
        try {
            $user = Auth::user();

            return response()->json([
                    'message' => 'Profile User berhasil diambil',
                    'data' => new UserResource($user)
                ], 200); //berhasil login
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi_kesalahan',
                'error' => $e->getMessage()
            ], 500); //jika terjadi kesalahan
        }
    }
    
    public function logout()
    {
        try {

            $user = Auth::user();
            $user->currentAccessToken()->delete(); //menghapus api token yang sudah login

            return response()->json([
                    'message' => 'Logout berhasil',
                    'data' => null
                ], 200); //berhasil login
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi_kesalahan',
                'error' => $e->getMessage()
            ], 500); //jika terjadi kesalahan
        }
    }

    public function register(RegisterStoreRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction(); //jika terjadi kesalahan input tidak langsung ke database

        try {
            $user = new User;
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']); //hanya bisa mengisi name, email, password lalu save
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken; // jika berhasil regis akan dibuatkan token

            DB::commit(); // memasukan ke database

            return response()->json([
                'message' => 'Reistrasi berhasil',
                'data' => [
                    'token' => $token,
                    'user' => new UserResource($user)
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi_kesalahan',
                'error' => $e->getMessage()
            ], 500); //jika terjadi kesalahan
        }
    }
}
