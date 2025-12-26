<?php

namespace App\Http\Controllers;

use App\Models\ServiceProvider;
use Illuminate\Http\Request;

class AppDownloadController extends Controller
{
    public function showProvider($id)
    {
        $provider = ServiceProvider::findOrFail($id);

        return view('app-download', [
            'provider' => $provider,
            'providerName' => $provider->name,
            'providerImage' => $provider->getFirstMediaUrl('images'),
        ]);
    }
}
