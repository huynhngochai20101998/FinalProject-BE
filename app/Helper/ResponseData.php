<?php

namespace App\Helper;

class ResponseData
{
    public static function dataResponseSuccess($token)
    {
        return response()->json([
            'message' => 'Successfully',
            'data' => [
                'user' => [
                    'id' => auth()->user()->id,
                    'email' => auth()->user()->email,
                    'created_at' => auth()->user()->created_at,
                    'updated_at' => auth()->user()->updated_at,
                    'role' => auth()->user()->role
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
            'result' => true
        ], 200);
    }

    public static function dataResponseFail()
    {
        return response()->json([
            'message' => 'Invalid data',
            'result' => false
        ], 401);
    }
}
