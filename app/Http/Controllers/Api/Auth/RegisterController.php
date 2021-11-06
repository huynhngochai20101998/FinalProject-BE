<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error.', $validator->errors(), 403);
        }

        $data = $request->all();

        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);

        $token = $user->createToken('LaravelAuthApp')->accessToken;

        return $this->sendResponseUser($token, $user, 'Register successfully.');
    }
}
