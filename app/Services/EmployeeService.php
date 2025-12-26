<?php

namespace App\Services;

use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\ServiceProvider;
use Illuminate\Http\UploadedFile;

class EmployeeService
{
    public function getEmployees(int $provider_id)
    {
        $employees = Employee::where('provider_id', $provider_id)->with('services')->get();

        return EmployeeResource::collection($employees);
    }

    public function getEmployeesByService(int $service_id, int $provider_id)
    {
        $employees = Employee::where('provider_id', $provider_id)
            ->whereHas('services', function ($query) use ($service_id) {
                $query->where('services.id', $service_id);
            })
            ->get();

        return EmployeeResource::collection($employees);
    }

    public function createEmployee(ServiceProvider $provider, $name, $email, $phone_number, $profile_picture = null, $service_ids = null)
    {

        $employee = Employee::create([
            'name' => $name,
            'email' => $email,
            'phone_number' => $phone_number,
            'provider_id' => $provider->id,
        ]);

        //EmployeeObserver.php will create a user for the employee

        if ($profile_picture && $profile_picture instanceof UploadedFile && $profile_picture->isValid()) {
            $employee->addMedia($profile_picture)->preservingOriginal()->toMediaCollection('profile_pictures');
        }

        if ($service_ids && is_array($service_ids)) {
            // Only attach services that belong to the service provider
            $validServiceIds = $provider->services()->whereIn('services.id', $service_ids)->pluck('services.id');
            $employee->services()->sync($validServiceIds);
        }

        $employee->load('services');

        return new EmployeeResource($employee);

    }

    public function updateEmployee(
        ServiceProvider $provider,
        $employee_id,
        $name,
        $email,
        $phone_number,
        $profile_picture,
        $service_ids = null
    ) {

        $employee = Employee::where('provider_id', $provider->id)->where('id', $employee_id)->first();

        if (! $employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $employee->update([
            'name' => $name ?? $employee->name,
            'email' => $email ?? $employee->email,
            'phone_number' => $phone_number ?? $employee->phone_number,
        ]);

        //EmployeeObserver.php will update the user for the employee

        if ($profile_picture && $profile_picture instanceof UploadedFile && $profile_picture->isValid()) {
            $employee->addMedia($profile_picture)->preservingOriginal()->toMediaCollection('profile_pictures');
        }

        if ($service_ids !== null) {
            if (is_array($service_ids)) {
                // Only attach services that belong to the service provider
                $validServiceIds = $provider->services()->whereIn('services.id', $service_ids)->pluck('services.id');
                $employee->services()->sync($validServiceIds);
            } else {
                // If empty array or null is passed, detach all services
                $employee->services()->detach();
            }
        }

        $employee->load('services');

        return new EmployeeResource($employee);

    }

    public function deleteEmployee(ServiceProvider $provider, $employee_id)
    {

        $employee = Employee::where('provider_id', $provider->id)->where('id', $employee_id)->first();

        if (! $employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $user = $employee->user;

        if ($user) {
            $user->delete();
        }

        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully'], 200);

    }
}
