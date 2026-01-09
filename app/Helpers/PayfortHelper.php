<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class PayfortHelper
{
    public function generateSDKToken($device_id)
    {
        $url = config('services.payfort.sandbox_mode') ? 'https://sbpaymentservices.payfort.com/FortAPI/paymentApi' : 'https://paymentservices.payfort.com/FortAPI/paymentApi';
        // $data = [
        //     'service_command' => 'SDK_TOKEN',
        //     'access_code' => config('services.payfort.access_code'),
        //     'merchant_identifier' => config('services.payfort.merchant_identifier'),
        //     'language' => 'en',
        //     'device_id' => $device_id,
        // ];

        $data = [
            'access_code'        => config('services.payfort.access_code'),
            'device_id'          => $device_id,
            'language'           => 'en',
            'merchant_identifier'=> config('services.payfort.merchant_identifier'),
            'service_command'    => 'SDK_TOKEN',
        ];

        $data = array_filter($data);

        $data['signature'] = self::generateSignature($data);

         \Log::info('request data',  ['data' =>$data]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $data);

      //  $response = Http::timeout(10)->asForm()->post($url, $data);

        // $response = Http::withOptions([
        //     'headers' => ['Content-Type' => 'application/x-www-form-urlencoded']
        // ])->post($url, $data);

      //  \Log::info('PAYFORT RESPONSE',  ['response' =>$response->json()]);
        \Log::info('PAYFORT SDK TOKEN RESPONSE', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        return $response->json();

    }

   /* private function generateSignature($data): string
    {

        $sha_string = '';

        ksort($data);

        foreach ($data as $key => $value) {
            $sha_string .= "$key=$value";
        }

        $sha_string = config('services.payfort.request_sha_phrase').$sha_string.config('services.payfort.request_sha_phrase');

        return hash('sha256', $sha_string);

    }*/

    private static function generateSignature(array $data): string
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
