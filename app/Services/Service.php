<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\ServiceProviderResource;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Str;

class Service
{
    protected function findOrCreateUser($phone_number, $access_type)
    {
        $account = '';
        if ($access_type == 'provider') {
            $account = ServiceProvider::where('phone_number',
                $phone_number)->first() ?? ServiceProvider::create(['phone_number' => $phone_number]);

        }
        if ($access_type == 'employee') {
            $account = Employee::where('phone_number',
                $phone_number)->first() ?? Employee::create(['phone_number' => $phone_number]);

        }
        if ($access_type == 'customer') {
            $account = Customer::where('phone_number',
                $phone_number)->first() ?? Customer::create(['phone_number' => $phone_number,'refer_code' => $this->generateReferCode()]);
        }
        if ($account == '') {
            throw new \Exception(__('Invalid access type', [], request()->header('lang') ?? 'en'));
        }
        if ($account->is_blocked == 1) {
            throw new \Exception(__('your account is blocked contact admin', [], request()->header('lang') ?? 'en'));
        }

        return $account;
    }

    public function generateReferCode()
    {
        do {
            $referCode = Str::random(6);
        } while (Customer::where('refer_code', $referCode)->exists());
        return $referCode;
    }

    protected function findUser(string $phone_number, string $access_type): ServiceProvider|Employee|Customer
    {
        $account = '';
        if ($access_type == 'provider') {
            $account = ServiceProvider::where('phone_number', $phone_number)->first();
        }

        if ($access_type == 'employee') {
            $account = Employee::where('phone_number', $phone_number)->first();
        }

        if ($access_type == 'customer') {
            $account = Customer::where('phone_number', $phone_number)->first();
        }

        if ($account == null) {
            throw new \Exception(__('Invalid phone number', [], request()->header('lang') ?? 'en'));
        }

        return $account;
    }

    public function checkUser(User $user)
    {

        if ($user->customer) {
            return new CustomerResource($user->customer);
        }

        if ($user->employee) {
            return new EmployeeResource($user->employee);
        }

        if ($user->serviceProvider) {
            $user->load('activeSubscription');
            return new ServiceProviderResource($user->serviceProvider);
        }

        throw new \Exception(__('Invalid user', [], request()->header('lang') ?? 'en'));
    }

    /**
     * @throws \Exception
     */
    protected function deleteUser($user, $access_type): void
    {

        if ($access_type == 'provider') {
            $confirmed_appointments=$user->serviceProvider
                ->appointments()
                ->whereIn('status_id', [AppointmentStatus::Confirmed->value,AppointmentStatus::RescheduleRequest->value])
                ->count();
            if($confirmed_appointments>0)
            {
                throw new \Exception(__('you have confirmed appointments ,you should take action on it before delete account', [], request()->header('lang') ?? 'en'));
            }
            $user->serviceProvider->update(['phone_number' => $user->serviceProvider->phone_number.'_deleted_'.$user->serviceProvider->id]);
            $user->serviceProvider->delete();
            $user->delete();
        } elseif ($access_type == 'employee') {
            $user->employee->update(['phone_number' => $user->employee->phone_number.'_deleted_'.$user->employee->id]);
            $user->employee->delete();
            $user->delete();

        } elseif ($access_type == 'customer') {
            $user->customer->update(['phone_number' => $user->customer->phone_number.'_deleted_'.$user->customer->id]);
            $user->customer->delete();
            $user->delete();
        } else {
            throw new \Exception(__('Invalid access type', [], request()->header('lang') ?? 'en'));
        }
    }
}
