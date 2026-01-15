<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function success($message = null, $code = 200)
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }

    // return error response
    protected function error($message = null, $code = 400)
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }
}
