<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceProviderRequest;
use App\Http\Resources\ServiceProviderResource;
use App\Models\ServiceProvider;

class ServiceProviderController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', ServiceProvider::class);

        return ServiceProviderResource::collection(ServiceProvider::all());
    }

    public function store(ServiceProviderRequest $request)
    {
        $this->authorize('create', ServiceProvider::class);

        return new ServiceProviderResource(ServiceProvider::create($request->validated()));
    }

    public function show(ServiceProvider $serviceProvider)
    {
        $this->authorize('view', $serviceProvider);

        return new ServiceProviderResource($serviceProvider);
    }

    public function update(ServiceProviderRequest $request, ServiceProvider $serviceProvider)
    {
        $this->authorize('update', $serviceProvider);

        $serviceProvider->update($request->validated());

        return new ServiceProviderResource($serviceProvider);
    }

    public function destroy(ServiceProvider $serviceProvider)
    {
        $this->authorize('delete', $serviceProvider);

        $serviceProvider->delete();

        return response()->json();
    }
}
