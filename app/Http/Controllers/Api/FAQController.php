<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FAQService;

class FAQController extends Controller
{
    /**
     * get all faqs
     *
     * endpoint to get all faqs
     *
     * @header accept-language ar/en
     *
     * @group  FAQ
     *
     * @url /api/faqs
     *
     * @type GET
     *
     * @response 200 [ { "id": 1, "question": "Repellendus est aliquid fugiat repellendus. Deleniti dolorem doloremque veritatis rerum vel ad dolores nesciunt.", "answer": "Odit quia maiores tempore architecto aliquam. Est saepe nesciunt tenetur perferendis nobis reiciendis. Ab ad incidunt impedit." },]
     */
    public function index(FAQService $service)
    {
        try {
            return $service->index();
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
}
