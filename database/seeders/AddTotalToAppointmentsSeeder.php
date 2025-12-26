<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\AttachedService;
use Illuminate\Database\Seeder;

class AddTotalToAppointmentsSeeder extends Seeder
{
    public function run(): void
    {
        $appointments = Appointment::where('total', 0)->get();
        foreach ($appointments as $appointment) {
            $sum_of_services = AttachedService::where('service_provider_id',
                $appointment->serviceProvider->id)->whereIn('service_id',
                $appointment->services->pluck('id'))
                ->get()
                ->map(function ($service) use ($appointment) {
                    $appointmentService = AppointmentService::where('appointment_id', $appointment->id)
                        ->where('service_id', $service->service_id)
                        ->first();

                    $number_of_beneficiaries = $appointmentService ? $appointmentService->number_of_beneficiaries : 0;
                    $service->price *= $number_of_beneficiaries;
                    return $service;
                })
                ->sum('price');
            $appointment->total = $sum_of_services;
            $appointment->save();
        }

    }
}
