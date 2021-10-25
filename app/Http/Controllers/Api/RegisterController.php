<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Ramsey\Uuid\Uuid;

class RegisterController extends Controller
{
    /**
     * Register
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $data['password'] = bcrypt($request->password);
        $data['uuid'] = Uuid::uuid4();
        $user = User::create($data);

        $token = $user->createToken('LaravelAuthApp')->accessToken;

        return response()->json(['user' => $user, 'token' => $token], 200);
    }
}
