<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function success($data)
    {
        $response = [
            'message' => 'Success',
            'data' => $data,
            'result' => true
        ];
        return response($response, 200);
    }

    protected function error($error)
    {
        $response = [
            'message' => 'Error',
            'data' => [
                'error' => $error
            ],
            'result' => false
        ];
        return response($response, 500);
    }
}
