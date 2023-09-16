<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Province;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Controllers\ApiController;

class UserController extends ApiController
{
    public function users()
    {
        $users = User::paginate(2);
        return $this->successResponse([
            'users' => UserResource::collection($users),
            'links' => UserResource::collection($users)->response()->getData()->links,
            'meta' => UserResource::collection($users)->response()->getData()->meta
        ]);
    }

    public function show(User $user)
    {
        return $this->successResponse(new UserResource($user));
    }
}
