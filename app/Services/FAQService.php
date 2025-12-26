<?php

namespace App\Services;

use App\Http\Resources\FAQCollection;
use App\Models\FAQ;

class FAQService
{
    public function index()
    {
        $faqs = FAQ::all();

        return new FAQCollection($faqs);

    }
}
