<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Services\EmployeeService;

class EmployeeController extends Controller
{
    /**
     * get employees
     *
     * endpoint to get employees details depending on the provider
     *
     * @group employees
     *
     * @type GET
     *
     * @url api/providers/{provider_id}/employees
     *
     * @authenticated
     *
     * @param  provider_id integer required The id of the provider. Example: 1
     *
     * @response 200 { "data": [ { "id": 3, "name": "Ewald Reynolds", "profile_picture": "http://localhost:8001/assets/default.jpg", "provider_id": 1 }, { "id": 6, "name": "Dr. Melvina Cole", "profile_picture": "http://localhost:8001/assets/default.jpg", "provider_id": 1 } ] }
     */
    public function getEmployees(EmployeeService $employeeService, int $provider_id)
    {

        try {
            return $employeeService->getEmployees($provider_id);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }

    }

    /**
     * get employees by service
     *
     * endpoint to get employees who can perform a specific service at a provider
     *
     * @group employees
     *
     * @type GET
     *
     * @url api/services/{service_id}/employees
     *
     * @authenticated
     *
     * @param  service_id integer required The id of the service. Example: 1
     * @queryParam provider_id integer required The id of the provider. Example: 1
     *
     * @response 200 { "data": [ { "id": 3, "name": "Ewald Reynolds", "profile_picture": "http://localhost:8001/assets/default.jpg" }, { "id": 6, "name": "Dr. Melvina Cole", "profile_picture": "http://localhost:8001/assets/default.jpg" } ] }
     */
    public function getEmployeesByService(EmployeeService $employeeService, int $service_id, \Illuminate\Http\Request $request)
    {
        try {
            return $employeeService->getEmployeesByService($service_id, $request->provider_id);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * create employee
     *
     * endpoint to create an employee only provider can create an employee
     *
     * @group employees
     *
     * @type POST
     *
     * @authenticated
     *
     * @url api/provider/employees/create
     *
     * @bodyParam name string required The name of the employee. Example: John Doe
     * @bodyParam email string required The email of the employee. Example: test@test.com
     * @bodyParam phone_number string required The phone number of the employee. Example: 08012345678
     * @bodyParam profile_picture file The profile picture of the employee.
     * @bodyParam service_ids string The IDs of services to assign to the employee (comma-separated). Example: 1,2,3
     *
     * @response 200 {
     * "id": 4,
     * "name": "ahmed m",
     * "email": "ahmed@easlyory.com",
     * "phone_number": "54645645",
     * "profile_picture": "http://localhost:8000/assets/default.jpg",
     * "provider_name": "youssef"
     * }
     * */
    public function createEmployee(EmployeeService $employeeService, CreateEmployeeRequest $request)
    {

        try {
            return $employeeService->createEmployee(
                provider: auth()->user()->serviceProvider,
                name: $request->name,
                email: $request->email,
                phone_number: $request->phone_number,
                profile_picture: $request->file('profile_picture'),
                service_ids: $request->service_ids
            );
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }

    }

    /**
     * update employee
     *
     * endpoint to update an employee only provider can update an employee
     *
     * @group employees
     *
     * @type POST
     *
     * @authenticated
     *
     * @url api/provider/employees/{employee_id}/update
     *
     * @urlParam employee_id integer required The id of the employee. Example: 1
     *
     * @bodyParam name string  The name of the employee. Example: John Doe
     * @bodyParam email string  The email of the employee. Example: test@test.com
     * @bodyParam phone_number string  The phone number of the employee. Example: 08012345678
     * @bodyParam profile_picture file  The profile picture of the employee.
     * @bodyParam service_ids string The IDs of services to assign to the employee (comma-separated). Example: 1,2,3
     *
     * @response 200 {
     * "id": 4,
     * "name": "youssef",
     * "email": "younan@gmail.com",
     * "phone_number": "31231231232",
     * "profile_picture": "http://localhost:8000/storage/11/08.jpg",
     * "provider_name": null
     * }
     */
    public function updateEmployee(EmployeeService $employeeService, UpdateEmployeeRequest $request, $employee_id)
    {

        try {
            return $employeeService->updateEmployee(
                provider: auth()->user()->serviceProvider,
                employee_id: $employee_id,
                name: $request->name,
                email: $request->email,
                phone_number: $request->phone_number,
                profile_picture: $request->file('profile_picture'),
                service_ids: $request->service_ids
            );
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Delete employee
     *
     *
     * endpoint to delete an employee only provider can delete an employee
     *
     * @group employees
     *
     * @url api/provider/employees/{employee_id}/delete
     *
     * @authenticated
     *
     * @urlParam employee_id integer required The id of the employee. Example: 1
     *
     * @response 200 { "message": "Employee deleted successfully"}
     * @response 400 { "message": "Employee not found"}
     */
    public function deleteEmployee(EmployeeService $employeeService, $employee_id)
    {
        try {
            return $employeeService->deleteEmployee(
                provider: auth()->user()->serviceProvider,
                employee_id: $employee_id
            );
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
