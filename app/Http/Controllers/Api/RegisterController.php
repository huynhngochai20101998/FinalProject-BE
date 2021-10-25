<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;

class RegisterController extends Controller
{
    /**
     * Register
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $data['password'] = bcrypt($request->password);

        $user = User::create($data);

        $token = $user->createToken('LaravelAuthApp')->accessToken;

        $data = ['user' => $user, 'access_token' => $token];
        return $this->success($data);
    }
}
