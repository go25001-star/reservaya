<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RolEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        $credenciales = $request->only('email', 'password');

        $token = Auth::attempt($credenciales);

        if (! Auth::attempt($credenciales)) {
            return response()->json([
                'message' => 'Credenciales inválidas',
            ], 401);
        } else {
            return $this->responseWithToken($token);
        }
    }

    public function register(Request $request)
    {
       try {
            $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole(Role::findByName(RolEnum::USUARIO->value, 'api'));
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => $user,
        ], 201);
       } catch (\Throwable $th) {
             return response()->json([ $th->getMessage()],500);
       }
    }

    public function responseWithToken($token)
    {
        $user = auth('api')->user();

        $staff = \App\Models\StaffHotel::where('user_id', $user->id)->first();
        $hotelId = $staff ? $staff->hotel_id : null;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'hotel_id' => $hotelId,
            'token_expires' => Auth()->factory()->getTTL() * 60,
        ], 200);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json([
            'message' => 'Sesión Cerrada correctamente',
        ], 200);
    }

    public function refresh()
    {
        return $this->responseWithToken(auth()->refresh());
    }
}
