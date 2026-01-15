<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function privacyPolicy()
    {
        return view('settings.privacy_policy');
    }

    public function termsConditions()
    {
        return view('settings.terms_conditions');
    }

    public function contact()
    {
        return view('contact');
    }
}
