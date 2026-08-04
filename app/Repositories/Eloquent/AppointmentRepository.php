<?php
namespace App\Repositories\Eloquent;

use App\Models\Appointment;
use App\Repositories\Interfaces\AppointmentRepositoryInterface;
use Illuminate\Support\Collection;

class AppointmentRepository implements AppointmentRepositoryInterface
{
    public function getAllAppointments(){
        return Appointment::all();
    }

    public function getAppointmentById($id){
        return Appointment::find($id);
    }

    public function createAppointment(array $data){
        return Appointment::create($data);
    }

    public function updateAppointment($id, array $data){
        $appointment = Appointment::find($id);
        $appointment->update($data);
        return $appointment;
    }

    public function deleteAppointment($id){
        $appointment = Appointment::find($id);
        $appointment->delete();
        return true;
    }

    public function getAppointmentByDate($date): Collection{
        return Appointment::where('date', $date)->get();
    }

    public function isSlotTaken($date, $time): bool
    {
        return Appointment::where('date', $date)
            ->where('time', $time)
            ->exists();
    }

    public function getAppointmentByEmail($email): Collection{
        return Appointment::where('email', $email)->get();
    }

    public function getAvailableSlots($date): array
    {
        $bookedSlots = Appointment::where('date', $date)
            ->pluck('time')
            ->toArray();

        $slots = [];

        for ($time = '09:00'; $time <= '18:00'; $time = date('H:i', strtotime($time) + 3600)) {
            $slots[] = [
                'time'      => $time,
                'available' => !in_array($time, $bookedSlots, true),
            ];
        }

        return ['slots' => $slots];
    }
}