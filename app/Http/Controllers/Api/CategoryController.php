<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    /**
     * get categories
     *
     * endpoint to get all categories
     *
     * @type GET
     *
     * @url api/categories
     *
     * @group categories
     *
     * @authenticated
     */
    public function index(CategoryService $categoryService)
    {
        try {
            return $categoryService->index();
        } catch (\Exception $exception) {
            return $this->error(['message' => $exception->getMessage()], 500);
        }
    }

    /**
     * get init data
     *
     * endpoint to get initial data for the app
     *
     * @url api/init-data
     *
     * @group init
     *
     * @authenticated
     *
     * @type GET
     *
     * @response { "data": [ { "category_name": "provident", "min_price": null, "max_price": null, "currency": "SAR" }, { "category_name": "tempora", "min_price": null, "max_price": null, "currency": "SAR" }, { "category_name": "maiores", "min_price": null, "max_price": null, "currency": "SAR" }, { "category_name": "est", "min_price": null, "max_price": null, "currency": "SAR" }, { "category_name": "iure", "min_price": null, "max_price": null, "currency": "SAR" }, { "category_name": "distinctio", "min_price": null, "max_price": null, "currency": "SAR" }, { "category_name": "autem", "min_price": 100, "max_price": 500, "currency": "SAR" }, { "category_name": "rem", "min_price": null, "max_price": null, "currency": "SAR" }, { "category_name": "molestiae", "min_price": null, "max_price": null, "currency": "SAR" }, { "category_name": "natus", "min_price": null, "max_price": null, "currency": "SAR" } ] }
     */
    public function getInitData(CategoryService $categoryService)
    {
        try {
            return $categoryService->getInitData();
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
}
