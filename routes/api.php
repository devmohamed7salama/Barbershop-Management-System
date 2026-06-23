<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// services routes crud
Route::get('/services', [ServicesController::class, 'index']);
Route::get('/services/{id}', [ServicesController::class, 'show']);
Route::post('/services/create', [ServicesController::class, 'store']);
Route::post('/services/update/{id}', [ServicesController::class, 'update']);
Route::post('/services/delete/{id}', [ServicesController::class, 'destroy']);

// customers routes crud
Route::get('/customers', [CustomerController::class, 'index']);
Route::get('/customers/{id}', [CustomerController::class, 'show']);
Route::post('/customers/create', [CustomerController::class, 'store']);
Route::post('/customers/update/{id}', [CustomerController::class, 'update']);
Route::post('/customers/delete/{id}', [CustomerController::class, 'destroy']);

// barber routes crud
Route::get('/barbers', [BarberController::class, 'index']);
Route::get('/barbers/{id}', [BarberController::class, 'show']);
Route::post('/barbers/create', [BarberController::class, 'store']);
Route::post('/barbers/update/{id}', [BarberController::class, 'update']);
Route::post('/barbers/delete/{id}', [BarberController::class, 'destroy']);

//appoiments routes crud
Route::get('/appoiments', [AppointmentController::class, 'index']);
Route::get('/appoiments/{id}', [AppointmentController::class, 'show']);
Route::post('/appoiments/create', [AppointmentController::class, 'store']);
Route::post('/appoiments/update/{id}', [AppointmentController::class, 'update']);
Route::post('/appoiments/delete/{id}', [AppointmentController::class, 'destroy']);
Route::post('/appoiments/status/{id}', [AppointmentController::class, 'updateStatus']);

//Invoice routes crud
Route::get('/invoices', [InvoiceController::class, 'index']);
Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
Route::post('/invoices/create', [InvoiceController::class, 'store']);
Route::post('/invoices/update/{id}', [InvoiceController::class, 'update']);
Route::post('/invoices/delete/{id}', [InvoiceController::class, 'destroy']);

//Shift routes crud
Route::get('/shifts', [ShiftController::class, 'index']);
Route::get('/shifts/{shift}', [ShiftController::class, 'show']);
Route::post('/shifts/start', [ShiftController::class, 'start']);
Route::post('/shifts/close', [ShiftController::class, 'close']);

//Dashboard routes
Route::get('/dashboard/stats', [DashboardController::class, 'index']);