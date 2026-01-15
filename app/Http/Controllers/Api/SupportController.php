<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Settings\SupportSettings;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * get support settings
     *
     * endpoint to get support settings
     *
     * @type GET
     *
     * @group Support
     *
     * @url /api/support
     *
     * @response 200 {"email":"youssef@bookly.com","phone_number":"123456789","whatsapp_phone_number":"987654321"}
     */
    public function get(SupportSettings $settings)
    {
        try {
            return response()->json([
                'email' => $settings->email,
                'phone_number' => $settings->phone_number,
                'whatsapp_phone_number' => $settings->whatsapp_phone_number,
                'app_store_link' => $settings->app_store_link,
                'google_play_link' => $settings->google_play_link,
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function redirect(SupportSettings $settings ,Request $request)
    {
        $userAgent = $request->header('User-Agent');

        if (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
            return  redirect($settings->app_store_link);
        } elseif (stripos($userAgent, 'Android') !== false) {
            return redirect($settings->google_play_link);
        } else {
            return redirect($settings->google_play_link);
        }
    }
}
