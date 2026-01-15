<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FAQController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PayfortController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Middleware\CheckBlockedMiddleware;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\GiftCardController;
use App\Http\Controllers\Api\FavouriteController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\BankDetailsController;
use App\Http\Controllers\Api\HomeSectionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProviderTypeController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\LoyaltyPointsController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\ServiceProviderController;
use App\Http\Controllers\Api\CustomerCampaignController;
use App\Http\Controllers\Api\PayfortSimulatorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => 'guest'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('verify-otp', [AuthController::class, 'verifyOTP']);
    Route::get('support', [SupportController::class,'get']);
    Route::get('app-download-redirect', [SupportController::class,'redirect']);
    Route::post('/feedback', [AppointmentController::class, 'getPayfortFeedback']);
    Route::get('/faqs', [FAQController::class, 'index']);
});

// Payfort Payment Simulator (Testing Only - Disable in Production)
if (!app()->environment('production')) {
    Route::prefix('simulator/payfort')->group(function () {
        Route::post('success', [PayfortSimulatorController::class, 'simulateSuccess']);
        Route::post('failure', [PayfortSimulatorController::class, 'simulateFailure']);
        Route::post('callback', [PayfortSimulatorController::class, 'simulateCallback']);
        Route::get('status/{type}/{id}', [PayfortSimulatorController::class, 'getPaymentStatus']);
    });
}

Route::get('/reviews', [ReviewController::class, 'index']);

Route::get('/campaign', [CustomerCampaignController::class, 'getCampaign']);
Route::get('/providers/types', [ProviderTypeController::class, 'index']);
Route::get('/providers/attached-services', [ServiceController::class, 'getAttachedServicesForProviders'])->middleware(['auth:sanctum',CheckBlockedMiddleware::class]);
Route::get('/providers', [ServiceProviderController::class, 'getProviders']);
Route::get('/providers/{id}/cancellation-policy', [ServiceProviderController::class, 'getCancellationPolicy']);
Route::get('/providers/{id}', [ServiceProviderController::class, 'getProviderById']);
Route::get('/home-sections', [HomeSectionController::class, 'index']);
Route::get('/init-data', [CategoryController::class, 'getInitData']);
Route::get('customer/attached-services', [ServiceController::class, 'getAttachedServiceForCustomers']);
Route::get('/attached-service/{attached_service_id}', [ServiceController::class, 'show']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{service_id}/employees', [EmployeeController::class, 'getEmployeesByService']);
Route::group(['middleware' => ['auth:sanctum',CheckBlockedMiddleware::class]], function () {
    Route::get('/cart', [\App\Http\Controllers\Api\CartController::class, 'index']);
    Route::post('/cart', [\App\Http\Controllers\Api\CartController::class, 'store']);
    Route::post('/cart/{cart_item_id}/update-quantity', [\App\Http\Controllers\Api\CartController::class, 'update']);
    Route::delete('/cart/{cart_item_id}', [\App\Http\Controllers\Api\CartController::class, 'destroy']);
    Route::delete('/clear-cart', [\App\Http\Controllers\Api\CartController::class, 'clearCart']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::delete('user', [AuthController::class, 'delete']);
    Route::get('wallet', [\App\Http\Controllers\Api\WalletController::class, 'index']);
    Route::get('wallet-transactions', [\App\Http\Controllers\Api\WalletController::class, 'walletTransactions']);
    Route::post('wallet/payout', [\App\Http\Controllers\Api\WalletController::class, 'cashout']);
    Route::get('wallet/payouts', [\App\Http\Controllers\Api\WalletController::class, 'getCashouts']);
    Route::get('wallet/payouts/{cashout}', [\App\Http\Controllers\Api\WalletController::class, 'showCashOut']);
    Route::post('wallet/add-balance', [\App\Http\Controllers\Api\WalletController::class, 'addBalance']);
    Route::group(['prefix' => 'user'], function () {
        Route::get('/', [AuthController::class, 'get']);
        Route::post('/', [AuthController::class, 'update']);
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/read-all-notifications', [NotificationController::class, 'readAll']);
    });

    Route::group(['prefix' => 'reviews'], function () {
        Route::post('/', [ReviewController::class, 'create']);
    });
    Route::group(['prefix' => 'addresses'], function () {
        Route::get('/', [AddressController::class, 'index']);
        Route::post('/', [AddressController::class, 'create']);
        Route::post('/update', [AddressController::class, 'update']);
        Route::delete('/{id}', [AddressController::class, 'destroy']);
        Route::get('/{provider_id}/address', [AddressController::class, 'serviceProviderAddress']);
    });

    Route::group(['prefix' => 'appointments'], function () {
        Route::get('/', [AppointmentController::class, 'get']);
        Route::get('/get-by-id', [AppointmentController::class, 'getById']);
        Route::get('/available-slots', [AppointmentController::class, 'getAvailableSlots']);
        Route::post('/hold-time-slot', [AppointmentController::class, 'holdTimeSlot']);
        //        Route::post('/hold', [AppointmentController::class, 'holdAppointment']);
        Route::post('/reschedule', [AppointmentController::class, 'reschedule']);
        Route::post('/reschedule/customer-response', [AppointmentController::class, 'customerRescheduleResponse']);

        Route::post('/', [AppointmentController::class, 'book']);
        Route::post('/{appointment}/cancel', [AppointmentController::class, 'cancel']);
        Route::post('/{appointment}/complete', [AppointmentController::class, 'complete']);
        Route::post('/{appointment}/confirm', [AppointmentController::class, 'confirm']);
        Route::post('/{appointment}/reject', [AppointmentController::class, 'reject']);
        Route::post('/{appointment}/payment-request', [AppointmentController::class, 'paymentRequest']);
        Route::post('/{appointment}/change-remaining-payment-method', [AppointmentController::class, 'changeRemainingPaymentMethod']);
        Route::get('/sdk-token', [AppointmentController::class, 'getSDKToken']);

        Route::get('/available-dates', [AppointmentController::class, 'getAvailableDates']);
    });

    Route::group(['prefix' => 'invoices'], function () {
        Route::get('/{invoice}/download', [InvoiceController::class, 'download']);
    });

    Route::group(['prefix' => 'providers'], function () {

        Route::post('/attached-services/{id}/update', [ServiceController::class, 'updateAttachedService']);
        Route::delete('/attached-services/{id}/delete', [ServiceController::class, 'destroy']);
        Route::post('/settings', [ServiceProviderController::class, 'changeServiceProviderSettings']);
        Route::get('/{provider_id}/employees', [EmployeeController::class, 'getEmployees']);
        Route::post('appointments/reject', [AppointmentController::class, 'rejectAppointmentByProvider']);
        Route::group(['prefix' => 'dashboard'], function () {
            Route::get('/', [ServiceProviderController::class, 'getServiceProviderDashboardInfo']);
            Route::get('/services/most-booked', [ServiceProviderController::class, 'getTheMostBookedService']);

            Route::group(['prefix' => 'appointments'], function () {
                Route::get('/confirmed/count',
                    [ServiceProviderController::class, 'countOfConfirmedAppointments']);
                Route::get('/used-gift-cards/count',
                    [ServiceProviderController::class, 'countOfUsedGiftCards']);
                Route::get('/cancelled/count',
                    [ServiceProviderController::class, 'countOfCancelledAppointments']);
                Route::get('/per-day/count',
                    [ServiceProviderController::class, 'countOfBookingsPerDay']);
                Route::get('/rejected/count',
                    [ServiceProviderController::class, 'countOfRejectedAppointments']);
                Route::get('/total-earnings',
                    [ServiceProviderController::class, 'getTotalEarnings']);
                Route::get('/delivery-types/most-preferred',
                    [ServiceProviderController::class, 'getTheMostPreferredServiceType']);
            });
        });

        Route::group(['prefix' => 'payouts'], function () {
            Route::get('/deferred', [ServiceProviderController::class, 'getDeferredPayouts']);
            Route::get('/deferred/total', [ServiceProviderController::class, 'getDeferredPayoutsTotal']);
            Route::get('/available', [ServiceProviderController::class, 'getAvailablePayouts']);
            Route::get('/available/total', [ServiceProviderController::class, 'getAvailablePayoutsTotal']);
            Route::get('/transferred', [ServiceProviderController::class, 'getTransferredPayouts']);
        });

        Route::group(['prefix' => 'employees'], function () {
            Route::post('/create', [EmployeeController::class, 'createEmployee']);
            Route::post('{employee_id}/update', [EmployeeController::class, 'updateEmployee']);
            Route::delete('{employee_id}/delete', [EmployeeController::class, 'deleteEmployee']);
        });


    });

    Route::group(['prefix' => 'customers'], function () {
        Route::group(['prefix' => 'favourites'], function () {
            Route::get('/', [FavouriteController::class, 'index']);
            Route::post('/{service_id}', [FavouriteController::class, 'store']);
            Route::delete('/{service_id}', [FavouriteController::class, 'delete']);
        });
    });
    Route::group(['prefix' => 'gift-cards'], function () {
        Route::get('/', [GiftCardController::class, 'index']);
        Route::get('/{id}', [GiftCardController::class, 'show']);
        Route::post('/', [GiftCardController::class, 'create']);
        Route::post('/redeem', [GiftCardController::class, 'redeem']);
        Route::get('/used/get', [GiftCardController::class, 'usedGiftCards']);
        Route::get('/received/get', [GiftCardController::class, 'receivedGiftCards']);
        Route::get('/themes/get', [GiftCardController::class, 'giftCardThemes']);
    });
    Route::group(['prefix' => 'loyalty-points'], function () {
        Route::get('/transactions', [LoyaltyPointsController::class, 'transactions']);
        Route::get('/discounts/show-by-id/{id}', [LoyaltyPointsController::class, 'show']);
        Route::post('/discounts/redeem', [LoyaltyPointsController::class, 'redeem']);
        Route::get('/discounts/get', [LoyaltyPointsController::class, 'loyaltyDiscounts']);
        Route::get('/discounts/redeemed', [LoyaltyPointsController::class, 'redeemedLoyaltyDiscounts']);
        Route::get('/discounts/redeemed-available', [LoyaltyPointsController::class, 'getAvailableRedeemedLoyaltyDiscounts']);
        Route::post('/discounts/verify', [LoyaltyPointsController::class, 'verifyLoyaltyDiscount']);
    });

    Route::post('promo-code/verify', [App\Http\Controllers\Api\PromoCodeController::class, 'verifyPromoCode']);

    Route::post('/attach-service', [ServiceController::class, 'attachService']);

    Route::get('/categories', [CategoryController::class, 'index']);

    Route::get('/payment-methods', PaymentMethodController::class);

    //subscriptions
    Route::get('/plans', PlanController::class);
    Route::get('/active-subscription', [SubscriptionController::class, 'activeSubscription']);
    Route::post('/subscriptions/create', [SubscriptionController::class, 'create']);

    Route::group(['prefix' => 'service'], function () {
        Route::post('/add-service-off-hours', [ServiceController::class, 'addServiceOffHours']);
        Route::get('/get-service-off-hours', [ServiceController::class, 'getServiceOffHours']);
        Route::post('/update-service-off-hour', [ServiceController::class, 'updateServiceOffHour']);
        Route::post('/delete-service-off-hour', [ServiceController::class, 'deleteServiceOffHour']);
    });

    Route::group(['prefix' => 'bank-details'], function () {
        Route::get('/', [BankDetailsController::class, 'index']);
        Route::post('/', [BankDetailsController::class, 'create']);
        Route::post('/update', [BankDetailsController::class, 'update']);
        Route::delete('/{id}', [BankDetailsController::class, 'destroy']);
    });

    // Chat routes
    Route::group(['prefix' => 'chat'], function () {
        Route::post('/send', [ChatController::class, 'sendMessage']);
        Route::get('/conversation/{appointmentId}', [ChatController::class, 'getConversation']);
        Route::get('/conversations', [ChatController::class, 'getConversations']);
    });
});

// Route::post('/refund', [PayfortController::class, 'refund']);
// Route::get('/callback-cancel', [PayfortController::class, 'callbackCancel']);
// Route::get('/callback-reject', [PayfortController::class, 'callbackReject']);

