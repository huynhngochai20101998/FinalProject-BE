<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helper\ResponseData;

class LoginController extends Controller
{
    /**
     * Login
     */

    public function __construct()
    {
        $this->get_fail = ResponseData::dataResponseFail();
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
            $get_success = ResponseData::dataResponseSuccess($token);
            return $get_success;
        } else {
            return $this->get_fail;
        }
    }

    /**
     * Logout
     */

    public function logout()
    {
        if (Auth::check()) {
            Auth::user()->token()->revoke();
            return response()->json([
                'success' => 'Logout success',
                'result' => true
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorised',
                'result' => false
            ], 401);
        }
    }
}
