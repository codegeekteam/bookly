<?php

    namespace App\Traits;

    trait HandleResponse
    {
        function send_response($success,$response_code,$message,$data,$http_code=200)
        {
            return response()->json(
                array(
                    'success'=>$success,
                    'response_code'=>$response_code,
                    'message'=>$message,
                    'data' => $data
                ),$http_code
            );
        }

    }
