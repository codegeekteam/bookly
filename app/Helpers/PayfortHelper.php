<?php

namespace App\Helpers;

use App\Models\PaymentLog;
use App\Models\Appointment;
use Illuminate\Support\Facades\Http;

class PayfortHelper
{
    public function generateSDKToken($device_id)
    {
        $url = config('services.payfort.sandbox_mode') ? 'https://sbpaymentservices.payfort.com/FortAPI/paymentApi' : 'https://paymentservices.payfort.com/FortAPI/paymentApi';
        $data = [
            'service_command' => 'SDK_TOKEN',
            'access_code' => config('services.payfort.access_code'),
            'merchant_identifier' => config('services.payfort.merchant_identifier'),
            'language' => 'en',
            'device_id' => $device_id,
        ];      

        $data['signature'] = self::generateSignature($data);

        //  \Log::info('request data',  ['data' =>$data]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $data);  

   
        \Log::info('PAYFORT SDK TOKEN RESPONSE STATUS', [
            'status' => $response->status(),
           // 'body'   => $response->body(),
        ]);

        return $response->json();

    }

   //  private static function generateSignature(array $data): string
     public static function generateSignature(array $data): string
    {
        ksort($data);

        $shaString = config('services.payfort.request_sha_phrase');

        foreach ($data as $key => $value) {
            $shaString .= $key . '=' . $value;
        }

        $shaString .= config('services.payfort.request_sha_phrase');

        return hash('sha256', $shaString);
    }   

}
