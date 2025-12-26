<?php

namespace App\Helpers;

use Http;

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

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $data);

        return $response->json();

    }

    private function generateSignature($data): string
    {

        $sha_string = '';

        ksort($data);

        foreach ($data as $key => $value) {
            $sha_string .= "$key=$value";
        }

        $sha_string = config('services.payfort.request_sha_phrase').$sha_string.config('services.payfort.request_sha_phrase');

        return hash('sha256', $sha_string);

    }
}
