<?php
    namespace App\Services;

    use Illuminate\Support\Facades\App;

    class SendSmsService
    {
        /**
         * Send a sms message to the given mobile.
         *
         * @param string $mobile
         * @param String $msg
         * @return \Illuminate\Http\JsonResponse
         */
        public static function toSms($number, $msg)
        {
            try
            {
                if(App::environment('production'))
                {
                    $fields = array(
                        "userName" => config('sms.msegat.userName'),
                        "numbers" => $number,
                        "userSender" => config('sms.msegat.userName'),
                        "apiKey" => config('sms.msegat.key'),
                        "msg" => $msg,
                        "msgEncoding" => "UTF8"
                    );
                    $postvars = http_build_query($fields);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, config('sms.msegat.link'),);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HEADER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            "Content-Type: application/json",
                        ));
                    $response = curl_exec($ch);
                    if(curl_errno($ch))
                    {
                        return response()->json(['status' => FALSE, 'message' => $ch]);
                    }
                    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    if(intval($httpcode) != 200)
                    {
                        return response()->json(['status' => FALSE, 'message' => $httpcode]);
                    }
                    $text = '';
                    $start = '{';
                    $end = '}';
                    $pattern = sprintf('/%s(.+?)%s/ims', preg_quote($start, '/'), preg_quote($end, '/'));
                    if(preg_match($pattern, $response, $matches))
                    {
                        list(, $match) = $matches;
                        $text = $match;
                    }
                    $text = "{" . $text . "}";
                    $response_array = json_decode($text);
                    if(intval($response_array->code) != 1)
                    {
                        return response()->json(['status' => FALSE, 'message' => $response_array->message]);
                    }
                }
                return response()->json(['status' => TRUE]);
            }
            catch (\Throwable $th)
            {
                throw $th;
            }
        }

        public static function sendSms($number, $msg)
        {
            try
            {
                if(App::environment('production'))
                {
                    $fields = array(
                        "userName" => config('sms.msegat.userName'),
                        "numbers" => $number,
                        "userSender" => config('sms.msegat.userName'),
                        "apiKey" => config('sms.msegat.key'),
                        "msg" => $msg,
                        "msgEncoding" => "UTF8"
                    );
                    $postvars = http_build_query($fields);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, config('sms.msegat.link'),);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HEADER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            "Content-Type: application/json",
                        ));
                    $response = curl_exec($ch);
                    if(curl_errno($ch))
                    {
                        return response()->json(['status' => FALSE, 'message' => $ch]);
                    }
                    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    if(intval($httpcode) != 200)
                    {
                        return response()->json(['status' => FALSE, 'message' => $httpcode]);
                    }
                    $text = '';
                    $start = '{';
                    $end = '}';
                    $pattern = sprintf('/%s(.+?)%s/ims', preg_quote($start, '/'), preg_quote($end, '/'));
                    if(preg_match($pattern, $response, $matches))
                    {
                        list(, $match) = $matches;
                        $text = $match;
                    }
                    $text = "{" . $text . "}";
                    $response_array = json_decode($text);
                    if(intval($response_array->code) != 1)
                    {
                        return response()->json(['status' => FALSE, 'message' => $response_array->message]);
                    }
                }
            }
            catch (\Throwable $th)
            {
                throw $th;
            }
        }
    }
