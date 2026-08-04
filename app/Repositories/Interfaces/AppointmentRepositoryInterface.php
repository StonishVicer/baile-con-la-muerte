<?php

namespace App\Repositories\Interfaces;
use Illuminate\Support\Collection;
interface AppointmentRepositoryInterface
{
    public function getAllAppointments();
    public function getAppointmentById($id);
    public function createAppointment(array $data);
    public function updateAppointment($id, array $data);
    public function deleteAppointment($id);
    public function getAppointmentByDate($date): Collection;
    public function isSlotTaken($date, $time): bool;
    public function getAppointmentByEmail($email): Collection;
    public function getAvailableSlots($date): array;
}