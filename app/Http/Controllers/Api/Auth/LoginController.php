<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Login
     */

    public function login(Request $request)
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
            if (auth()->user()->hasVerifiedEmail()) {
                return $this->sendResponseUser($token, auth()->user(), 'Login successfully.');
            } else {
                return $this->sendError('Your email address is not verified.', ['access_token' => $token], 403);
            }
        }

        return $this->sendError('Error', ['error' => 'Validation error.'], 403);
    }

    /**
     * Logout
     */

    public function logout()
    {
        if (Auth::check()) {
            $logout = Auth::user()->token()->revoke();
            return $this->sendResponse($logout, 'Logout successfully.');
        } else {
            return $this->sendError('Unauthorised', ['error' => 'Unauthorised'], 401);
        }
    }
}
