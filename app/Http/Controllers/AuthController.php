<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,id',
            'password' => 'required|string',
            'c_password' => 'required|same:password',
            'address' => 'required|string',
            'cellphone' => 'required|integer',
            'postal_code' => 'required|integer',
            'province_id' => 'required|integer',
            'city_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'address' => $request->address,
            'cellphone' => $request->cellphone,
            'postal_code' => $request->postal_code,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id
        ]);

        $token = $user->createToken('myDevice')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json('user not found', 401);
        }

        $token = $user->createToken('myDevice')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 201);
    }
    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json('logged out', 200);
    }
}
