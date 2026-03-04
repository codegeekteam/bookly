<?php  

namespace App\Traits;

use App\Helpers\PayfortHelper;
use App\Helpers\RefundHelper;
use App\Models\Appointment;
use App\Models\PaymentLog;
use App\Models\RefundLog;
use Illuminate\Support\Facades\Http;

trait RefundTrait
{    
    public function initiateRefund(Appointment $appointment, $type)
    {
        $paymentLog = PaymentLog::where('appointment_id', $appointment->id)->first();
        if(!$paymentLog || $paymentLog->merchant_reference == null) {
              \Log::info('paymentLog data insufficient');
              return false;
        }
        //  $description = json_decode($appointment->service?->title, true);
        $total = 0;
        if($type == 'reject') {
        $total = $appointment->total_payed ?? 0;
        }
        if($type == 'cancel') {
            // Determine who is cancelling
        $isProviderCancelling = ($appointment->serviceProvider->user_id === auth()->id());

        // Calculate refund based on cancellation policy
        $cancellationPolicyService = new \App\Services\CancellationPolicyService();
        $refundInfo = $cancellationPolicyService->calculateRefund($appointment, $isProviderCancelling);
            if ($appointment->payment_status == 'paid' || $appointment->payment_status == 'partially_paid') {
                $refundAmount = $refundInfo['refund_amount'] ?? 0;
                if ($refundInfo['refund_percentage'] == 100 && $refundAmount > 0) {        
                    $total = $refundAmount;
                }
            }
        }

        $amount = round($total) * 100; //converted to sub unit

        $base_url = config('services.payfort.refund_url').'/FortAPI/paymentApi';
        $refund_data = [            
                        'command' => 'REFUND',
                        'access_code' =>  config('services.payfort.access_code'),
                        'merchant_identifier' =>  config('services.payfort.merchant_identifier'),
                        'merchant_reference' => $paymentLog->merchant_reference,
                        'amount' =>  $amount,
                        'currency' =>  'SAR',
                        'language' => 'en',
                        'fort_id' =>  $paymentLog->fort_id,            
                    ];
        $refund_data['signature'] = PayfortHelper::generateSignature($refund_data);
        $refund_data['order_description'] =  $paymentLog->appointment_id . '- Refund Request Processed'; 
        \Log::info('REQUEST DATA : '.json_encode($refund_data));
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($base_url, $refund_data); 

        //    $response = Http::asForm()->post(config('payfort.endpoint'), $params);

        \Log::info('REFUND PROCESSED RESPONSE STATUS', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);
        if (!$response->successful()) {
            \Log::error('Refund API call failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        }
       $responseData = $response->json();
       if (!isset($responseData['merchant_reference'])) {
            \Log::info('Refund response missing merchant_reference');
            return false;
        }
        // Split the merchant_reference into type and identifier
        $parts = explode('_', $responseData['merchant_reference']);

        if (count($parts) < 2) {
            \Log::info('Invalid ID format');
            return false;
        }
        if(count($parts) == 3) {
            $type = 'appointment'; 
            $identifier = $parts[1];
            $paymentType = 'remaining';
        }elseif(count($parts) == 2) {
             $type = $parts[0];
            $identifier = $parts[1];
        }      
        if($responseData['response_code'] == '06000') {
        $refundHelper = new RefundHelper;
        RefundLog::create([
           'response_code' => $responseData['response_code'],
           'response_message' => $responseData['response_message'],
           'amount' => $responseData['amount'],
           'status' => $responseData['status'],
           'merchant_reference' => $responseData['merchant_reference'],          
           'response' => json_encode($responseData),
           'model_type' => $refundHelper->getMorphClassFromType($type),
           'model_id' => $identifier,
        ]);        
        return true;
        }else {
            \Log::info('Refund Failed');
             return false;
        }
        //  return $response->json();

    }
}