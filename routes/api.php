<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/appointments', [\App\Http\Controllers\AppointmentController::class, 'index']);

Route::post('/appointments', [\App\Http\Controllers\AppointmentController::class, 'store']);

Route::get('/appointments/{id}', [\App\Http\Controllers\AppointmentController::class, 'show']);

Route::put('/appointments/{id}', [\App\Http\Controllers\AppointmentController::class, 'update']);

Route::delete('/appointments/{id}', [\App\Http\Controllers\AppointmentController::class, 'destroy']);

Route::get('/available-slots/{date}', [\App\Http\Controllers\AppointmentController::class, 'availableSlots']);