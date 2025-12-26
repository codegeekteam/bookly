<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentRescheduleRequest;
use App\Http\Requests\BookAppointmentRequest;
use App\Http\Requests\ChangeRemainingPaymentMethodRequest;
use App\Http\Requests\RescheduleAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Notifications\AppointmentNotification;
use App\Notifications\CancelAppointmentNotification;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

class AppointmentController extends Controller
{


    /**
     * get available slots
     *
     *
     * endpoint to get the available slots of a specific provider
     *
     * @type GET
     *
     * @url api/appointments/available
     *
     * @queryParam provider_idrequired
     * @queryParam service_idrequired
     * @queryParam daterequired
     *
     * @group appointments
     *
     * @response "slots": [ "10:30 am", "11:00 am", "11:30 am", "12:00 pm", "12:30 pm", "01:30 pm", "02:00 pm", "02:30 pm", "03:30 pm", "04:00 pm", "05:00 pm", "05:30 pm", "06:00 pm", "06:30 pm", "07:00 pm" ]
     *
     * @authenticated
     */
    public function getAvailableSlots(AppointmentService $appointmentService, Request $request)
    {
        try {
            return $appointmentService->getAvailableSlots(
                provider_id: $request->provider_id,
                service_id: $request->service_id,
                date: $request->date,
                employee_id: $request->employee_id
            );
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function holdTimeSlot(AppointmentService $appointmentService, Request $request)
    {
        $this->validate($request, [
            'provider_id' => 'required',
            'service_id' => 'required',
            'date' => 'required',
            'time' => 'required',
        ]);
        try {
            return $appointmentService->holdTimeSlot(provider_id: $request->provider_id,
                service_id: $request->service_id, date: $request->date, time: $request->time);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /**
     * get appointments
     *
     *
     * endpoint to get the appointments of logged in user
     *
     * @type GET
     *
     * @authenticated
     *
     * @group appointments
     *
     * @url api/appointments
     *
     * @response 200 [ { "id": 1, "date": "2023-11-26", "services": [ { "name": "accusamus", "price": 100, "service_beneficiaries": 1, "selected_employee": null, "date": "2023-11-20", "start_time": "09:00:00", "end_time": "10:00:00", "location": "123, Desert Boulevard, Riyadh, 67890, Saudi Arabia", "employee": null } ], "promo_code": "XISM2000", "discount": 200, "payment_method": "Cash", "total": { "amount": 100, "currency": "SAR" }, "provider": { "id": 8, "name": "reprehenderit", "image": "http://localhost:8000/assets/default.jpg", "type": "enterprise" }, "has_review": null, "comment": null, "status": "confirmed", "created_at": "19-11-2023 12:48" }, { "id": 8, "services": [], "promo_code": "XISM2000", "discount": 200, "payment_method": "Cash", "total": { "amount": 0, "currency": "SAR" }, "provider": { "id": 2, "name": "laudantium", "image": "http://localhost:8000/assets/default.jpg", "type": "freelancer" }, "has_review": null, "comment": null, "status": "confirmed", "created_at": "19-11-2023 12:48" } ]
     */
    public function get(AppointmentService $appointmentService)
    {
        try {
            return $appointmentService->get(auth()->user());
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    #[Endpoint('get-by-id')]
    #[Authenticated]
    #[ResponseFromApiResource(AppointmentResource::class, Appointment::class)]
    public function getById(AppointmentService $appointmentService, Request $request)
    {
        try {
            return $appointmentService->getById(auth()->user(),$request);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
    /**
     * hold appointment
     *
     *
     * endpoint to hold an appointment for the customer till he confirms it
     *
     * @type POST
     *
     * @group appointments
     *
     * @authenticated
     *
     * @url api/appointments/hold
     *
     * @queryParam appointment_idrequired
     *
     * @response { "data": { "id": 1, "date": "19-09-2023", "time_from": "16:21", "time_to": "16:21", "service": "ea", "customer": "Craig", "on_hold": false, "created_at": "19-09-2023 16:21", "updated_at": "19-09-2023 17:21" } }
     */
    public function holdAppointment(AppointmentService $appointmentService, Request $request)
    {
        try {
            return $appointmentService->holdAppointment($request);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /**
     * Get SDK Token
     *
     * endpoint to generate the SDK token for payfort PG.Token is sent to the Fort Payment SDK as part of the payment process.Token is sent to Fort Payment SDK for validation of token and device id. please revise further in https://docs.payfort.com/docs/api/build/index.html#before-you-start-the-integration180
     *
     * @type GET
     *
     * @group appointments
     *
     * @url api/appointments/sdk-token
     *
     * @authenticated
     *
     * @queryParam device_id required the device id
     *
     * @response 200 { "payment": { "sdk_token": "3d215d996a92476ebe2e0a748c0f11ad", "response_message": "Success", "response_code": "22000" } }
     * @response 400 { "message": "Invalid device id" }
     */
    public function getSDKToken(AppointmentService $appointmentService, Request $request)
    {
        try {
            return $appointmentService->getSDKToken(device_id: $request->device_id);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /**
     * cancel appointment
     *
     *
     * endpoint to cancel an appointment
     *
     * @type POST
     *
     * @group appointments
     *
     * @authenticated
     *
     * @url api/appointments/{appointment}/cancel
     *
     * @response 200 { "message": "Appointment cancelled successfully" }
     * @response 400 { "message": "Invalid state transition" }
     */
    public function cancel(Appointment $appointment)
    {
        try {
            $appointment->state()->cancel();
            $appointment->customer->user->notify(new CancelAppointmentNotification($appointment));
            $appointment->serviceProvider->user->notify(new CancelAppointmentNotification($appointment));
            return response()->json(['message' => __('Appointment cancelled successfully')]);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /**
     * book appointment
     *
     *
     * endpoint to book an appointment,a single appointment may have multiple provided services services on various times however these reserved services are provided in one single date and the same provider.
     *
     *
     * @url api/appointments
     *
     * @type POST
     *
     * @group appointments
     *
     * @authenticated
     *
     * @bodyParam services array required the id of the services, these services must belong to same provider
     * @bodyParam promo_code string the promo code
     * @bodyParam comment string  the comment
     * @bodyParam payment_method_id integer required the id of the payment method
     * @bodyParam response_code string required the response code acquired from Flutter SDK response parameters
     * @bodyParam amount string required the amount acquired from Flutter SDK response parameters
     *
     * @response 200 { "id": 1, "services": [ { "name": "accusamus", "price": 100, "service_beneficiaries": 1, "selected_employee": null, "date": "2023-11-20", "start_time": "09:00:00", "end_time": "10:00:00", "location": "123, Desert Boulevard, Riyadh, 67890, Saudi Arabia", "employee": null } ], "promo_code": "XISM2000", "discount": 200, "payment_method": "Cash", "total": { "amount": 100, "currency": "SAR" }, "provider": { "id": 8, "name": "reprehenderit", "image": "http://localhost:8000/assets/default.jpg", "type": "enterprise" }, "has_review": null, "comment": null, "status": "confirmed", "created_at": "19-11-2023 12:48" }, { "id": 8, "services": [], "promo_code": "XISM2000", "discount": 200, "payment_method": "Cash", "total": { "amount": 0, "payment_status": unpaid/partially_paid/paid  ,"currency": "SAR" }, "provider": { "id": 2, "name": "laudantium", "image": "http://localhost:8000/assets/default.jpg", "type": "freelancer" }, "has_review": null, "comment": null, "status": "confirmed", "created_at": "19-11-2023 12:48" }
     */
    public function book(AppointmentService $appointmentService, BookAppointmentRequest $request)
    {
        try {
            return $appointmentService->book(
                customer: auth()->user()->customer,
                services: $request->services,
                promo_code: $request->promo_code,
                comment: $request->comment,
                payment_method_id: $request->payment_method_id,
                loyalty_discount_customer_id: $request->loyalty_discount_customer_id,
            );
        } catch (\Exception $exception) {
            Log::critical($exception->getMessage(), [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
            return $this->error($exception->getMessage());
        }

    }

    /**
     * reschedule appointment
     *
     * endpoint to reschedule an appointment
     *
     * @type POST
     *
     * @group appointments
     *
     * @authenticated
     *
     * @url api/appointments/reschedule
     *
     * @bodyParam appointment_id integer required the id of the appointment
     * @bodyParam service_id integer required the id of the service
     * @bodyParam employee_id integer the id of the employee
     * @bodyParam timeslot string required the timeslot that can be obtained from available slots endpoint
     *
     * @response 200 { "id": 12, "date": "2024-04-08", "services": [ { "name": "eligendi", "price": 500, "service_beneficiaries": 1, "selected_employee": null, "date": "2024-04-08", "start_time": "10:00 pm", "end_time": "11:00 pm", "delivery_type_id": 1, "delivery_type": "My Place", "location": "123, Desert Boulevard, Riyadh, 67890, Saudi Arabia", "employee": null } ], "promo_code": "XISM2000", "discount": 0, "payment_method": "Cash", "total": { "amount": 500, "payment_status": "unpaid", "currency": "SAR" }, "provider": { "id": 1, "name": "quia", "image": "http://localhost:8000/assets/default.jpg", "type": "freelancer" }, "has_review": null, "comment": "dont ring the bell", "status": "pending", "created_at": "2024-04-08T10:57:47.000000Z" }
     * @response 400 {"message": "Appointment not found"}
     * @response 400 {"message": "Appointment is not pending"}
     * @response 400 {"message": "Time slot is not available"}
     * @response 400 {"message": "Service not found"}
     */
    public function reschedule(AppointmentService $appointmentService, RescheduleAppointmentRequest $request)
    {
        try {
            return $appointmentService->reschedule(
                provider: auth()->user()->serviceProvider,
                appointment_id: $request->appointment_id,
                service_id: $request->service_id,
                employee_id: $request->employee_id,
                date: $request->date,
                timeslot: $request->timeslot,
            );
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /**
     * complete appointment
     *
     *
     * endpoint to complete an appointment
     *
     * @type POST
     *
     * @group appointments
     *
     * @authenticated
     *
     * @url api/appointments/{appointment}/complete
     *
     * @response 200 { "message": "Appointment completed successfully" }
     * @response 400 { "message": "Invalid state transition" }
     */
    public function complete(Appointment $appointment, AppointmentService $appointmentService)
    {
        try {

            return $appointmentService->markAsComplete($appointment);

        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /**
     * get payfort feedback
     *
     * @type POST
     *
     * @url api/feedback
     *
     * @group appointment
     *
     * @response 200 { "message": "Appointment completed successfully" }
     */
    public function getPayfortFeedback(AppointmentService $appointmentService, Request $request)
    {
        try {
            return $appointmentService->getPayfortFeedback(
                response_code: $request->input('response_code'),
                id: $request->input('merchant_reference'),
                amount: $request->input('amount'),
            );
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /**
     * confirm appointment
     *
     *
     * endpoint to confirm an appointment
     *
     * @type POST
     *
     * @group appointments
     *
     * @authenticated
     *
     * @url api/appointments/{appointment}/confirm
     *
     * @response 200 { "message": "Appointment confirmed successfully" }
     * @response 400 { "message": "Invalid state transition" }
     */
    public function confirm(Appointment $appointment)
    {
        try {
            $appointment->state()->confirm();

            return response()->json(['message' => __('Appointment confirmed successfully')]);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /**
     * reschedule appointment response
     *
     * endpoint to reschedule an appointment by customer
     *
     * @type POST
     *
     * @param AppointmentService $appointmentService
     * @param AppointmentRescheduleRequest $request
     * @return \App\Http\Resources\AppointmentResource|\Illuminate\Http\JsonResponse
     *
     * @bodyParam appointment_id integer required the id of the appointment
     * @bodyParam service_id integer required the id of the service
     * @bodyParam customer_response string either accept or reject
     *
     * @url api/appointments/reschedule-response
     *
     * @authenticated
     *
     * @response 200 { "message": "Appointment rescheduled successfully" }
     *
     * @response 400 { "message": "Invalid state transition" }
     */

    public function customerRescheduleResponse(
        AppointmentService $appointmentService,
        AppointmentRescheduleRequest $request
    )
    {
        try {
            return $appointmentService->customerRescheduleResponse(
                appointment_id: $request->appointment_id,
                service_id: $request->service_id,
                customer_id: auth()->user()->customer->id,
                customer_response: $request->customer_response,
            );
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }


    /**
     * reject appointment
     *
     *
     * endpoint to reject an appointment
     *
     * @type POST
     *
     * @group appointments
     *
     * @authenticated
     *
     * @url api/appointments/{appointment}/reject
     *
     * @response 200 { "message": "Appointment rejected successfully" }
     * @response 400 { "message": "Invalid state transition" }
     */
    public function reject(Appointment $appointment)
    {
        try {
            $appointment->state()->reject();

            return response()->json(['message' => __('Appointment rejected successfully')]);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /**
     * get available dates
     *
     *
     * endpoint to get the available slots of a specific provider
     *
     * @type GET
     *
     * @url api/appointments/available-dates
     *
     * @queryParam provider_id required
     * @queryParam service_id required
     * @queryParam date required
     *
     * @group appointments
     *
     * @response 200 { "available_dates": [ "2024-09-19", "2024-09-22", "2024-09-23", "2024-09-26", "2024-09-29", "2024-09-30" ] }
     *
     * @authenticated
     */
    public function getAvailableDates(AppointmentService $appointmentService, Request $request)
    {
        try {
            return $appointmentService->getAvailableDates(provider_id: $request->provider_id,
                service_id: $request->service_id, date: $request->date);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

//    /*
//     * reject appointment by provider
//     *
//     * endpoint to reject an appointment by provider
//     *
//     * @type POST
//     *
//     * @group appointments
//     *
//     * @authenticated
//     *
//     * @url api/providers/appointments/reject
//     *
//     * @bodyParam appointment_id integer required the id of the appointment
//     *
//     * @response 200 { "message": "Appointment cancelled successfully" }
//     * @response 400 { "message": "Invalid state transition" }
//     */
//
//    public function rejectAppointmentByProvider(
//        AppointmentService $service,
//       $appointment
//    ) {
//        try {
//            return $service->rejectAppointmentByProvider(
//                appointment: $appointment,
//                provider_id: auth()->user()->serviceProvider->id,
//            );
//        } catch (\Exception $exception) {
//            return $this->error($exception->getMessage());
//        }
//
//    }

    /**
     * Change remaining payment method
     *
     * endpoint to change the payment method for the remaining payment of an appointment
     *
     * @type POST
     *
     * @group appointments
     *
     * @authenticated
     *
     * @url api/appointments/{appointment}/change-remaining-payment-method
     *
     * @bodyParam payment_method_id integer required the id of the new payment method Example: 2
     *
     * @response 200 {
     *   "message": "Remaining payment method updated successfully",
     *   "appointment": {
     *     "id": 123,
     *     "remaining_payment_method": "Cash",
     *     "remaining_payment_status": "pending",
     *     "remaining_amount": 350.00
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "remaining_payment_status": ["The remaining payment has already been processed and cannot be changed."]
     *   }
     * }
     */
    #[Endpoint('change-remaining-payment-method')]
    #[Authenticated]
    #[ResponseFromApiResource(AppointmentResource::class, Appointment::class)]
    public function changeRemainingPaymentMethod(
        ChangeRemainingPaymentMethodRequest $request,
        Appointment $appointment,
        AppointmentService $appointmentService
    ) {
        try {
            return $appointmentService->changeRemainingPaymentMethod(
                appointment: $appointment,
                payment_method_id: $request->payment_method_id
            );
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

}
