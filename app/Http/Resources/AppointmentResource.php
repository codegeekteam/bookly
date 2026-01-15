<?php

namespace App\Http\Resources;

use App\Models\AppointmentService;
use App\Models\AttachedService;
use App\Models\DeliveryType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Appointment */
class AppointmentResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        // Fetch attached services for the provider
        $attached_services = AttachedService::where('service_provider_id', $this->serviceProvider->id)
            ->whereIn('service_id', $this->services->pluck('id'))
            ->get();

        // Extract date from the first service's pivot
        $date = $this->services->first()?->pivot?->date;

        // Retrieve all delivery types
        $deliveryTypes = DeliveryType::all();

        return [
            'id' => $this->id,
            'date' => $date,
            'services' => $this->getServices($attached_services, $request, $deliveryTypes),
            'promo_code' => optional($this->promoCode)->code ?? '',
            'loyalty_discount_customer_id' => $this->loyalty_discount_customer_id,
            'discount' => (float) $this->discount,
            'payment_method' => $this->paymentMethod?$this->paymentMethod->name:'Card',
            'deposit_payment_method' => $this->depositPaymentMethod?$this->depositPaymentMethod->name:null,
            'remaining_payment_method' => $this->remainingPaymentMethod?$this->remainingPaymentMethod->name:null,
            'remaining_to_pay' => $this->calculateRemainingToPay(),
            'total' => $this->getTotal(),
            'provider' => $this->getProvider(),
            'has_review' => $this->status?->title !== 'completed' ? null : $this->review()->exists(),
            'comment' => $this->comment,
            'status' => $this->status?->title,
            'created_at' => $this->created_at,
            'customer' => $this->customer ? new CustomerResource($this->customer) : null,
            'payment_status' => $this->payment_status,
            'conversation_id' => $this->conversation?->id,
            'has_active_chat' => $this->conversation && $this->conversation->is_active,
            'unread_messages_count' => $this->getUnreadMessagesCount($request),
            'invoice_url' => $this->invoice ? $this->invoice->getPdfUrl() : null,
            'cancellation_policy' => $this->getCancellationPolicy(),
        ];
    }

    private function getServices($attached_services, Request $request, $deliveryTypes)
    {
        return $attached_services->flatMap(function ($attached_service) use ($request, $deliveryTypes) {
            // Retrieve all appointment services for this attached service
            return $this->appointmentServices->where('service_id', $attached_service->service_id)->map(function ($appointmentService) use ($attached_service, $deliveryTypes, $request) {
                $employee = $appointmentService->employee;
                return [
                    'id' => $attached_service->service?->id,
                    'name' => $attached_service->service?->getTranslation('title', $request->header('lang') ?? 'en'),
                    'price' => $attached_service->price,
                    'has_deposit' => $attached_service->has_deposit,
                    'deposit' => $attached_service->deposit,
                    'deposit_amount' => $attached_service->deposit_amount,
                    'service_beneficiaries' => $appointmentService->number_of_beneficiaries,
                    'selected_employee' => null,
                    'date' => Carbon::parse($appointmentService->date)->format('Y-m-d'),
                    'is_rescheduled' => $appointmentService->new_start_time !== null,
                    'reschedule_data' => $this->getRescheduleData($appointmentService),
                    'start_time' => Carbon::parse($appointmentService->start_time)->format('h:i a'),
                    'end_time' => Carbon::parse($appointmentService->end_time)->format('h:i a'),
                    'delivery_type_id' => $appointmentService->delivery_type_id,
                    'delivery_type' => optional($deliveryTypes->firstWhere('id', $appointmentService->delivery_type_id))->title ?? null,
                    'location' => '123, Desert Boulevard, Riyadh, 67890, Saudi Arabia',
                    'employee' => $employee ? [
                        'id' => $employee->id,
                        'name' => $employee->name,
                        'profile_picture' => $employee->getFirstMediaUrl('profile_pictures') ?: asset('assets/default.jpg'),
                    ] : null,
                    'service_type' => $appointmentService->number_of_beneficiaries > 1 ? 'For Group' : 'For You',
                    'address' => $appointmentService->address ? new AddressResource($appointmentService->address) : null,
                ];
            });
        });
    }

    private function getTotal()
    {
        return [
            'amount_due' => (float) $this->amount_due,
            'amount' => (float) $this->total,
            'total_payed' => (float) $this->total_payed,
            'wallet_amount' => (float) $this->wallet_amount,
            'card_amount' => (float) $this->card_amount,
            'discount' => (float) $this->discount,
            'sub_total' => round($this->total + $this->discount, 2),
            'payment_status' => $this->payment_status,
            'deposit_amount' => (float) $this->deposit_amount,
            'deposit_payment_status' => $this->deposit_payment_status,
            'remaining_amount' => (float) $this->remaining_amount,
            'remaining_payment_status' => $this->remaining_payment_status,
            'currency' => 'SAR',
        ];
    }

    private function getProvider()
    {
        return [
            'id' => $this->serviceProvider->id,
            'name' => $this->serviceProvider->name,
            'phone_number' => $this->serviceProvider->phone_number,
            'image' => $this->serviceProvider->getMedia('images')->last()?->getUrl() ?? asset('assets/default.jpg'),
            'type' => $this->serviceProvider->providerType?->title,
        ];
    }

    private function getRescheduleData($appointmentService)
    {
        return $this->when($appointmentService->new_start_time, [
            'new_start_time' => Carbon::parse($appointmentService->new_start_time)->format('h:i a'),
            'new_end_time' => Carbon::parse($appointmentService->new_end_time)->format('h:i a'),
            'new_date' => Carbon::parse($appointmentService->new_date)->format('Y-m-d'),
            'accepted_reschedule' => $appointmentService->accepted_reschedule,
            'previous_status_id' => $this->previousStatus?->title,
        ]);
    }

    private function calculateRemainingToPay()
    {
        // If payment is already complete, return 0
        if ($this->payment_status === 'paid') {
            return 0;
        }

        // Calculate the remaining amount to be paid
        $totalAmount = (float) $this->total;
        $totalPaid = (float) $this->total_payed;

        $remainingAmount = max(0, $totalAmount - $totalPaid);

        return round($remainingAmount, 2);
    }

    private function getUnreadMessagesCount(Request $request)
    {
        if (!$this->conversation) {
            return 0;
        }

        $user = auth()->user();
        $userEntity = $user ? ($user->customer ?: $user->serviceProvider) : null;

        return $userEntity ? $this->conversation->getUnreadCountForUser($userEntity) : 0;
    }

    private function getCancellationPolicy()
    {
        $cancellationPolicyService = new \App\Services\CancellationPolicyService();
        return $cancellationPolicyService->getPolicyInfo($this->resource);
    }
}
