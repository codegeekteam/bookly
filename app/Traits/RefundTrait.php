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
        if(!$paymentLog || $paymentLog->mechant_reference == null) {
              \Log::info('paymentLog data insufficient');
              return false;
        }
        //  $description = json_decode($appointment->service?->title, true);
        if($type == 'reject') {
        $total = $appointment->total_payed;
        }
        if($type == 'cancel') {
            // Determine who is cancelling
        $isProviderCancelling = ($appointment->serviceProvider->user_id === auth()->id());

        // Calculate refund based on cancellation policy
        $cancellationPolicyService = new \App\Services\CancellationPolicyService();
        $refundInfo = $cancellationPolicyService->calculateRefund($appointment, $isProviderCancelling);
            if ($appointment->payment_status == 'paid' || $appointment->payment_status == 'partially_paid') {
                $refundAmount = $refundInfo['refund_amount'];
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
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($base_url, $refund_data); 

        //    $response = Http::asForm()->post(config('payfort.endpoint'), $params);

        \Log::info('REFUND PROCESSED RESPONSE STATUS', [
            'status' => $response->status(),
           // 'body'   => $response->body(),
        ]);
       
        // Split the merchant_reference into type and identifier
        $parts = explode('_', $response['merchant_reference']);

        if (count($parts) < 2) {
            return response()->json(['message' => 'Invalid ID format'], 200);
            // return response()->json(['message' => 'success'], 200);
        }
        if(count($parts) == 3) {
            $type = 'appointment'; 
            $identifier = $parts[1];
            $paymentType = 'remaining';
        }elseif(count($parts) == 2) {
             $type = $parts[0];
            $identifier = $parts[1];
        }      
        if($response['response_code'] == '06000') {
        $refundHelper = new RefundHelper;
        RefundLog::create([
           'response_code' => $response['response_code'],
           'response_message' => $response['response_message'],
           'amount' => $response['amount'],
           'status' => $response['status'],
           'merchant_reference' => $response['merchant_reference'],          
           'response' => json_encode($response),
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