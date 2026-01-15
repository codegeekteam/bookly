<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\VerifyOTPRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Login
     *
     * Endpoint to join the app with phone number
     *
     * @type POST
     *
     * @url api/login
     *
     * @group Auth
     *
     * @bodyParam phone_number string required The phone number of the user. Example: 553498000
     * @bodyParam access_type string required The access type of the user. Example: customer, service_provider, employee
     */
    public function login(LoginRequest $request, AuthService $authService)
    {
        try {
            $authService->login($request->phone_number, $request->access_type);

            return $this->success('OTP sent');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Verify OTP
     *
     * Endpoint to verify the OTP sent to the user
     *
     * @group Auth
     *
     * @type POST
     *
     * @url api/verify-otp
     *
     * @bodyParam phone_number string required The phone number of the user. Example: 553498000
     * @bodyParam access_type string required The access type of the user. Example: customer, provider, employee
     * @bodyParam otp string required The OTP sent to the user. Example: 123456
     * @bodyParam firebase_token string .
     */
    public function verifyOTP(VerifyOTPRequest $request, AuthService $authService)
    {
        try {
            return $authService->verifyOTP(phone_number: $request->phone_number, otp: $request->otp,
                access_type: $request->access_type,firebase_token:$request->firebase_token);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Logout
     *
     * Endpoint to logout the user
     *
     * @group Auth
     *
     * @type POST
     *
     * @url api/logout
     *
     * @authenticated
     */
    public function logout(Request $request, AuthService $authService)
    {
        try {
            $authService->logout();

            return $this->success('Logged out');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Get User
     *
     * Endpoint to get the customer data
     *
     * @group Auth
     *
     * @type GET
     *
     * @url api/user
     *
     * @authenticated
     */
    public function get(AuthService $authService)
    {
        try {
            return $authService->get(auth()->user());
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Update User
     *
     * Endpoint to update the user data whether it's customer, service provider or employee
     *
     * @group Auth
     *
     * @type POST
     *
     * @url api/user
     *
     * @authenticated
     *
     * @bodyParam first_name string The first name of the user. Example: John
     * @bodyParam last_name string The last name of the user. Example: Doe
     * @bodyParam date_of_birth date The date of birth of the customers only. Example: 1990-01-01
     * @bodyParam name string The name of the user in case if youre entering for service provider or employee. Example: John Doe
     * @bodyParam email string The email of the user. Example:
     * @bodyParam phone_number string The phone number of the user. Example: 553498000
     * @bodyParam provider_id integer The provider id of the employee (used if the sign is the access_type is employee). Example: 1
     * @bodyParam provider_type_id integer The provider type id of the service provider (freelancer,organisation) (used if the sign if the access_type is service_provider). Example: 1
     * @bodyParam commercial_register string The commercial register of the service provider. Example: 123456789
     * @bodyParam profile_picture file path to profile picture
     *
     * @response 200 as provider { "data": { "id": 2, "name": "youssef ibrahim", "is_blocked": false, "is_active": true, "email": "re@youssef.com", "phone_number": "987654321", "biography": "hello there", "address": null, "commercial_register": "123456789", "twitter": "twitter", "snapchat": "snapchat.net", "instagram": null, "tiktok": "tiktok.net", "average_rate": 0, "images": [ "http://localhost:8000/assets/default.jpg" ], "profile_picture": "http://localhost:8000/storage/17/OQ22bK4PpUyXhDxO.jpg", "services": [], "provider_type": null, "created_at": "2023-11-16 15:07:45", "updated_at": "2023-11-16 15:08:44", "profile_complete_percentage": 71 } }
     * @response 200 as customer { "id": 2, "first_name": "youssef", "last_name": "ibrahim", "email": "re@youssef.com", "date_of_birth": null, "is_blocked": false, "created_at": "2023-11-16 13:58:33", "updated_at": "2023-11-16 13:59:30", "profile_picture": "http://localhost:8000/storage/15/uQBjcnru3ZqcR6RK.jpg", "profile_complete_percentage": 75 }
     */
    public function update(UpdateUserRequest $request, AuthService $authService)
    {

        try {
            return $authService->update(user: auth()->user(), first_name: $request->first_name,
                last_name: $request->last_name, date_of_birth: $request->date_of_birth, name: $request->name,
                email: $request->email, phone_number: $request->phone_number,
                provider_id: $request->provider_id, provider_type_id: $request->provider_type_id,
                commercial_register: $request->commercial_register, twitter: $request->twitter,
                instagram: $request->instagram,
                tiktok: $request->tiktok,
                snapchat: $request->snapchat,
                biography: $request->biography,
                profile_picture: $request->file('profile_picture'),
                refer_code: $request->refer_code,
            );

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Delete user account
     *
     * Endpoint for deleting user account
     * @group Auth
     * @authenticated
     *
     *
     */
    public function delete(DeleteUserRequest $request, AuthService $authService){
        try {
            $authService->delete(
                user : auth()->user(),
                access_type : $request->access_type,
            );
            return $this->success('Account deleted successfully');
        }catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
