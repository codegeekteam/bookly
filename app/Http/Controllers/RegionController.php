<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegionRequest;
use App\Http\Resources\RegionResource;
use App\Models\Region;

class RegionController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Region::class);

        return RegionResource::collection(Region::all());
    }

    public function store(RegionRequest $request)
    {
        $this->authorize('create', Region::class);

        return new RegionResource(Region::create($request->validated()));
    }

    public function show(Region $region)
    {
        $this->authorize('view', $region);

        return new RegionResource($region);
    }

    public function update(RegionRequest $request, Region $region)
    {
        $this->authorize('update', $region);

        $region->update($request->validated());

        return new RegionResource($region);
    }

    public function destroy(Region $region)
    {
        $this->authorize('delete', $region);

        $region->delete();

        return response()->json();
    }
}
