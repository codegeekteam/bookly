<?php

namespace App\Services;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\InitDataResource;
use App\Models\Category;

class CategoryService
{
    public function index()
    {
        $categories = \App\Models\Category::where('is_active',true)->get();

        return CategoryResource::collection($categories);

    }

    public function getInitData()
    {
        $categories = Category::with(['services.attachedServices'])->where('is_active',true)->get();

        return InitDataResource::collection($categories);

    }
}
