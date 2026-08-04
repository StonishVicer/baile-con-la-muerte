<?php

namespace App\Services;

use App\Repositories\Interfaces\AppointmentRepositoryInterface;
use Carbon\Carbon;

class AppointmentService
{
    protected $appointmentRepository;

    public function __construct(AppointmentRepositoryInterface $appointmentRepository)
    {
        $this->appointmentRepository = $appointmentRepository;
    }

    public function getAllAppointments()
    {
        return $this->appointmentRepository->getAllAppointments();
    }

    public function getAppointmentById($id)
    {
        return $this->appointmentRepository->getAppointmentById($id);
    }

    public function getAvailableSlotsForDate($date)
    {
        $carbonDate = Carbon::parse($date);
        if (!$carbonDate->isWeekday()) {
            throw new \Exception('La fecha debe ser un día hábil (lunes a viernes).');
        }

        $allSlots = [];
        $start = Carbon::parse('09:00');
        $end = Carbon::parse('18:00');
        while ($start <= $end) {
            $allSlots[] = $start->format('H:i');
            $start->addHour();
        }

        $bookedSlots = $this->appointmentRepository
            ->getAppointmentByDate($date)
            ->pluck('time')
            ->map(fn($time) => Carbon::parse($time)->format('H:i'))
            ->toArray();

        $isToday = $carbonDate->isToday();
        $now = Carbon::now();

        $slots = [];
        foreach ($allSlots as $time) {
            $isBooked = in_array($time, $bookedSlots);
            $isPast = false;

            if ($isToday) {
                $isPast = Carbon::parse($date . ' ' . $time)->lessThan($now);
            }

            $slots[] = [
                'time'      => $time,
                'available' => !$isBooked && !$isPast,
            ];
        }

        return [
            'date'  => $date,
            'slots' => $slots,
        ];
    }

    public function createAppointment(array $data)
    {
        $date = $data['date'];
        $time = $data['time'];
        $email = $data['email'];

        $carbonDate = Carbon::parse($date);
        if (!$carbonDate->isWeekday()) {
            throw new \Exception('La fecha debe ser un día hábil (lunes a viernes).');
        }

        $carbonTime = Carbon::parse($time);
        $start = Carbon::parse('09:00');
        $end = Carbon::parse('18:00');
        if ($carbonTime < $start || $carbonTime > $end) {
            throw new \Exception('La hora debe estar entre 09:00 y 18:00.');
        }

        if ($carbonDate->isToday()) {
            $slotDateTime = Carbon::parse($date . ' ' . $time);
            if ($slotDateTime->lessThan(Carbon::now())) {
                throw new \Exception('No se puede reservar un horario que ya ha pasado.');
            }
        }

        if ($carbonTime->minute !== 0) {
            throw new \Exception('La hora debe ser en punto (ej. 09:00, 10:00, etc.).');
        }

        if ($this->appointmentRepository->isSlotTaken($date, $time)) {
            throw new \Exception('El horario seleccionado ya está ocupado.');
        }

        $existing = $this->appointmentRepository->getAppointmentByEmail($email);
        if ($existing->isNotEmpty()) {
            throw new \Exception('Este correo electrónico ya tiene una cita registrada.');
        }

        return $this->appointmentRepository->createAppointment([
            'name'  => $data['name'],
            'email' => $data['email'],
            'date'  => $date,
            'time'  => $time,
        ]);
    }
}