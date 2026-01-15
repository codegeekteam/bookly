<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAttachedServiceRequest;
use App\Services\ServiceService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * get services
     *
     *
     * endpoint to get all services and search based on name or category based on list of categories and if the 2 params are empty it returns all services
     *
     * @authenticated
     *
     * @type GET
     *
     * @group services
     *
     * @url api/services
     *
     * @queryParam keyword(optional)
     * @queryParam category_id(optional)
     * @queryParam sort_direction(optional) only accepts asc or desc
     *
     * @response { "data": [ { "id": 2, "title": "repellat", "is_active": false, "created_at": "2023-09-14 09:28:13", "updated_at": "2023-09-14 09:28:13" } ] }
     */
    public function index(ServiceService $serviceService, Request $request)
    {
        try {
            return $serviceService->index(keyword: $request->keyword, category_id: $request->category_id,
                sort_direction: $request->sort_direction);
        } catch (\Exception $exception) {
            return $this->error(['message' => $exception->getMessage()], 500);
        }
    }

    /**
     * Get Attached Service By id
     *
     * @group services
     *
     * @urlParam attached_service_id required The id of the attached service example: 1
     *
     * @response {"data":{"id":1,"service":"molestias","service_id":1,"service_description":"Reiciendis sit aut deserunt ut atque qui magni.","currency":"SAR","service_image":"https://bookly-dev.rizme-labs.xyz/assets/default.jpg","service_provider":"test","service_provider_type":"freelancer","service_provider_image":"https://bookly-dev.rizme-labs.xyz/assets/default.jpg","rating":null,"is_favourite":false,"price":150,"min_price":150,"max_price":200,"service_provider_id":1,"average_rate":null,"my_place":true,"customer_place":true,"operational_hours":[{"day":"SUN","start_time":"10:00","end_time":"12:00","duration_in_minutes":15},{"day":"MON","start_time":"10:00","end_time":"12:00","duration_in_minutes":15},{"day":"TUE","start_time":"10:00","end_time":"12:00","duration_in_minutes":15},{"day":"WED","start_time":"10:00","end_time":"12:00","duration_in_minutes":15},{"day":"THU","start_time":"10:00","end_time":"12:00","duration_in_minutes":15},{"day":"FRI","start_time":"10:00","end_time":"12:00","duration_in_minutes":15},{"day":"SAT","start_time":"10:00","end_time":"12:00","duration_in_minutes":15}]}}
     *
     * @return \App\Http\Resources\AttachedServiceResource|\Illuminate\Http\JsonResponse
     */
    public function show($attached_service_id, ServiceService $service)
    {
        try {
            return $service->getAttachedServiceById($attached_service_id);
        } catch (\Exception $exception) {
            return $this->error(['message' => $exception->getMessage()], 404);
        }
    }

    /**
     * get attached services for providers
     *
     * endpoint to get the attached service of the logged in provider
     *
     * @url api/providers/attached-services
     *
     * @type GET
     *
     * @authenticated
     *
     * @group services
     *
     * @response 200 { "data": [ { "id": 1, "service": "aperiam", "service_id": 1, "service_description": "Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Pellentesque commodo eros a enim. Cras sagittis. Morbi mollis tellus ac sapien. Nam at tortor in tellus interdum sagittis.", "currency": "SAR", "service_image": "http://localhost:8000/assets/default.jpg", "service_provider": "harum", "service_provider_type": "enterprise", "service_provider_image": "http://localhost:8000/assets/default.jpg", "rating": 5, "is_favourite": false, "price": 200, "min_price": 200, "max_price": 200, "service_provider_id": 1, "average_rate": 2, "my_place": false, "customer_place": false } ] }
     */
    public function getAttachedServicesForProviders(ServiceService $serviceService)
    {
        try {
            return $serviceService->getAttachedServicesForProviders(auth()->user()->serviceProvider);
        } catch (\Exception $exception) {
            return $this->error(['message' => $exception->getMessage()], 500);
        }
    }

    /**
     * get attached service for customers
     *
     *
     * endpoint to get the attached service for customers using service_id
     *
     * @url api/customer/attached-services
     *
     * @authenticated
     *
     * @type GET
     *
     * @group services
     *
     * @queryParam service_id(required) int  to get the attached service for customers
     * @queryParam keyword(optional) string searches using provider name
     * @queryParam sort_direction(optional) string only accepts asc or desc
     * @queryParam min_price(optional) int sets the minimum price
     * @queryParam max_price(optional) int sets the maximum price
     * @queryParam delivery_type_id(optional) array sets the delivery type id
     * @queryParam offers_filter(optional) boolean filter services that have promo codes
     * @queryParam nearest_appointment_filter(optional) boolean sort by nearest available appointment
     * @queryParam date_specific_search(optional) string filter by specific date (YYYY-MM-DD)
     *
     * @response 200 { "data": [ { "id": 1, "service": "aperiam", "service_id": 1, "service_description": "Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Pellentesque commodo eros a enim. Cras sagittis. Morbi mollis tellus ac sapien. Nam at tortor in tellus interdum sagittis.", "currency": "SAR", "service_image": "http://localhost:8000/assets/default.jpg", "service_provider": "harum", "service_provider_type": "enterprise", "service_provider_image": "http://localhost:8000/assets/default.jpg", "rating": 5, "is_favourite": false, "price": 200, "min_price": 200, "max_price": 200, "service_provider_id": 1, "average_rate": 2, "my_place": false, "customer_place": false ,"has_deposit": true, "deposit": "10.00"b } ] }
     */
    public function getAttachedServiceForCustomers(ServiceService $serviceService, Request $request)
    {
        try {
            return $serviceService->getAttachedServiceForCustomers(
                service_id: $request->service_id,
                keyword: $request->keyword,
                sort_direction: $request->sort_direction,
                min_price: $request->min_price,
                max_price: $request->max_price,
                delivery_type_id: $request->delivery_type_id,
                provider_id: $request->provider_id,
                offers_filter: $request->boolean('offers_filter', false),
                nearest_appointment_filter: $request->boolean('nearest_appointment_filter', false),
                date_specific_search: $request->date_specific_search
            );

        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }

    }

    /**
     * attach service
     *
     * endpoint to attach service to the logged in provider
     *
     * @type POST
     *
     * @group services
     *
     * @url api/attach-service
     *
     * @authenticated
     *
     * @bodyParam service_id int required
     * @bodyParam price float required
     * @bodyParam has_deposit int in:0,1
     * @bodyParam deposit float
     * @bodyParam delivery_types array required
     * @bodyParam description string optional
     * @bodyParam ops_hours array required
     * @bodyParam ops_hours.day string required Enum : SAT,SUN,MON,TUE,WED,THU,FRI
     * @bodyParam ops_hours.start_time string required
     * @bodyParam ops_hours.end_time string required
     * @bodyParam ops_hours.duration_in_minutes int required
     *
     * @response { "data": { "id": 17, "service": "quia", "service_provider": "youssef", "price": "500.00","has_deposit": true, "deposit": "10.00","delivery_types": [ { "id": 1, "title": "My Place", "created_at": "2023-09-14T15:18:35.000000Z", "updated_at": "2023-09-14T15:18:35.000000Z" }, { "id": 2, "title": "Customer's Place", "created_at": "2023-09-14T15:18:35.000000Z", "updated_at": "2023-09-14T15:18:35.000000Z" } ] } }
     */
    public function attachService(ServiceService $serviceService, Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'price' => 'required',
            'has_deposit' => 'nullable|integer|in:0,1',
            'deposit' => 'required_if:has_deposit,1|numeric|min:0|max:100',
            'delivery_types' => 'required',
            'description' => 'nullable',
            'ops_hours' => 'required|array',
            'ops_hours.*.day' => 'required|in:SAT,SUN,MON,TUE,WED,THU,FRI',
            'ops_hours.*.start_time' => 'required|date_format:H:i',
            'ops_hours.*.end_time' => 'required|date_format:H:i|after:from',
            'ops_hours.*.duration_in_minutes' => 'required|integer|min:1|max:1440',
        ]);
        try {
            return $serviceService->attachService(
                auth()->user(),
                $request->get('service_id'),
                $request->get('price'),
                $request->get('delivery_types'),
                $request->get('description'),
                $request->get('ops_hours'),
                $request->get('has_deposit'),
                $request->get('deposit'),
            );

        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /**
     * add service off hours
     *
     * endpoint to attach service to the logged in provider
     *
     * @type POST
     *
     * @group services
     *
     * @url api/service/add-service-off-hours
     *
     * @authenticated
     *
     * @bodyParam service_id int required
     * @bodyParam off_hours array required
     * @bodyParam off_hours.day string required Enum : SAT,SUN,MON,TUE,WED,THU,FRI
     * @bodyParam off_hours.start_time string required Example : 10:00
     * @bodyParam off_hours.end_time string required Example : 11:00
     *
     * @response 200 { "message": "Service off hours added successfully }
     * @response 404 { "message": "Service not attached'" }
     */
    public function addServiceOffHours(ServiceService $serviceService, Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'off_hours' => 'required|array',
            'off_hours.*.day' => 'required|in:SAT,SUN,MON,TUE,WED,THU,FRI',
            'off_hours.*.start_time' => 'required|date_format:H:i',
            'off_hours.*.end_time' => 'required|date_format:H:i|after:off_hours.*.start_time',
        ]);
        try {
            return $serviceService->addServiceOffHours(
                auth()->user(),
                $request->get('service_id'),
                $request->get('off_hours'),
            );

        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /**
     * update attached service
     *
     * endpoint to update attached service to the logged in provider
     *
     * @type POST
     *
     * @group services
     *
     * @url api/providers/attached-services/{id}/update
     *
     * @authenticated
     *
     * @urlParam id int required the id of the attached service
     *
     * @bodyParam price float nullable
     * @bodyParam deposit float nullable
     * @bodyParam has_deposit int nullable in:0,1
     * @bodyParam delivery_types array nullable
     * @bodyParam description string nullable
     * @bodyParam ops_hours array
     * @bodyParam ops_hours.day string in:SAT,SUN,MON,TUE,WED,THU,FRI
     * @bodyParam ops_hours.start_time string
     * @bodyParam ops_hours.end_time string
     * @bodyParam ops_hours.duration_in_minutes int
     */
    public function updateAttachedService(ServiceService $serviceService, $id, UpdateAttachedServiceRequest $request)
    {
        try {
            return $serviceService->updateAttachedService(
                user: auth()->user(),
                service_id: $id,
                price: $request->price,
                delivery_types: $request->delivery_types,
                description: $request->description,
                ops_hours: $request->ops_hours,
                has_deposit: $request->has_deposit,
                deposit : $request->deposit
            );

        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }

    }

    /**
     * Delete attached service by service id
     *
     * endpoint to delete the attached service
     *
     * @type DELETE
     *
     * @group services
     *
     * @url api/providers/attached-services/{id}/delete
     *
     * @authenticated
     *
     * @urlParam id int required the id of the attached service
     *
     * @response 200 { "message": "Service deleted successfully" }
     * @response 404 { "message": "Service not found" }
     */
    public function destroy(int $id, ServiceService $serviceService): ?\Illuminate\Http\JsonResponse
    {
        try {
            $isDeleted = $serviceService->delete($id);

            return $isDeleted
                ? $this->success('Service deleted successfully')
                : $this->error('Service not found', 404);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /**
     * get service off hours
     *
     * endpoint to attach service to the logged in provider
     *
     * @type GET
     *
     * @group services
     *
     * @url api/service/get-service-off-hours
     *
     * @authenticated
     *
     * @queryParam date string optional
     *
     * @response 200 { "data": [ { "id": 2, "service_id": 1, "day": "THU", "start_time": "15:00", "end_time": "16:00" }, { "id": 3, "service_id": 1, "day": "MON", "start_time": "10:00", "end_time": "11:00" }, { "id": 4, "service_id": 1, "day": "MON", "start_time": "10:00", "end_time": "11:00" }, { "id": 5, "service_id": 1, "day": "TUE", "start_time": "09:00", "end_time": "11:00" }, { "id": 6, "service_id": 1, "day": "WED", "start_time": "13:00", "end_time": "15:00" } ] }
     */
    public function getServiceOffHours(ServiceService $serviceService, Request $request)
    {
        try {
            return $serviceService->getServiceOffHours(
                auth()->user(),
                $request
            );

        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /**
     * delete service off hour
     *
     * endpoint to attach service to the logged in provider
     *
     * @type POST
     *
     * @group services
     *
     * @url api/service/delete-service-off-hours
     *
     * @authenticated
     *
     * @bodyParam id int required
     *
     * @response 200 { "message": 'this Service off hour deleted successfully' }
     * @response 422 { "message": "The selected id is invalid.", "errors": { "id": [ "The selected id is invalid." ] } }
     * @response 400 { "message": "this hour not found" }
     */
    public function deleteServiceOffHour(ServiceService $serviceService, Request $request)
    {
        $request->validate([
            'id' => 'required|exists:operational_off_hours,id',
        ]);
        try {
            return $serviceService->deleteServiceOffHour(
                auth()->user(),
                $request->get('id')
            );

        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /**
     * update service off hour
     *
     * endpoint to attach service to the logged in provider
     *
     * @type POST
     *
     * @group services
     *
     * @url api/service/update-service-off-hours
     *
     * @authenticated
     *
     * @bodyParam id int required
     * @bodyParam day string required Enum: SAT,SUN,MON,TUE,WED,THU,FRI
     * @bodyParam start_time string required Example : 10:00
     * @bodyParam end_time string required Example : 11:00
     *
     * @response 200 { "message": 'this Service off hour updated successfully }
     * @response 422 { "message": "The selected id is invalid.", "errors": { "id": [ "The selected id is invalid." ] } }
     * @response 400 { "message": "this hour not found" }
     */
    public function updateServiceOffHour(ServiceService $serviceService, Request $request)
    {
        $request->validate([
            'id' => 'required|exists:operational_off_hours,id',
            'day' => 'required|in:SAT,SUN,MON,TUE,WED,THU,FRI',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);
        try {
            return $serviceService->updateServiceOffHour(
                auth()->user(),
                $request
            );

        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
}
