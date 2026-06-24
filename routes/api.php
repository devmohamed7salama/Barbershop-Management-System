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

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/appointments', [AppointmentController::class, 'store']);
Route::post('/appoiments/create', [AppointmentController::class, 'store']);
Route::get('/appointments/queue-count', [AppointmentController::class, 'queueCount']);
Route::get('/services', [ServicesController::class, 'index']);
Route::get('/services/{id}', [ServicesController::class, 'show']);
Route::get('/barbers', [BarberController::class, 'index']);
Route::get('/barbers/{id}', [BarberController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Authenticated User Routes (Customers & Clients)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Client/User: View services



    // Client/User: View barbers



    // Client/User: Book & Manage appointments
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::get('/appoiments', [AppointmentController::class, 'index']);
    Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
    Route::get('/appoiments/{id}', [AppointmentController::class, 'show']);


    Route::put('/appointments/{id}', [AppointmentController::class, 'update']);
    Route::post('/appoiments/update/{id}', [AppointmentController::class, 'update']);
    Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);
    Route::post('/appoiments/delete/{id}', [AppointmentController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Administrative Routes (Admin CRUD & Operations)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Services CRUD (Admin)
    Route::post('/services', [ServicesController::class, 'store']);
    Route::post('/services/create', [ServicesController::class, 'store']);
    Route::put('/services/{id}', [ServicesController::class, 'update']);
    Route::post('/services/update/{id}', [ServicesController::class, 'update']);
    Route::delete('/services/{id}', [ServicesController::class, 'destroy']);
    Route::post('/services/delete/{id}', [ServicesController::class, 'destroy']);

    // Customers CRUD (Admin)
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::get('/customers/{id}', [CustomerController::class, 'show']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::post('/customers/create', [CustomerController::class, 'store']);
    Route::put('/customers/{id}', [CustomerController::class, 'update']);
    Route::post('/customers/update/{id}', [CustomerController::class, 'update']);
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);
    Route::post('/customers/delete/{id}', [CustomerController::class, 'destroy']);

    // Barbers CRUD (Admin)
    Route::post('/barbers', [BarberController::class, 'store']);
    Route::post('/barbers/create', [BarberController::class, 'store']);
    Route::put('/barbers/{id}', [BarberController::class, 'update']);
    Route::post('/barbers/update/{id}', [BarberController::class, 'update']);
    Route::delete('/barbers/{id}', [BarberController::class, 'destroy']);
    Route::post('/barbers/delete/{id}', [BarberController::class, 'destroy']);

    // Appointments CRUD & Status Control (Admin)
    Route::post('/appointments/status/{id}', [AppointmentController::class, 'updateStatus']);
    Route::post('/appoiments/status/{id}', [AppointmentController::class, 'updateStatus']);

    // Invoices CRUD (Admin)
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
    Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::post('/invoices/create', [InvoiceController::class, 'store']);
    Route::put('/invoices/{id}', [InvoiceController::class, 'update']);
    Route::post('/invoices/update/{id}', [InvoiceController::class, 'update']);
    Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy']);
    Route::post('/invoices/delete/{id}', [InvoiceController::class, 'destroy']);
    Route::post('/invoices/quick', [InvoiceController::class, 'quickStore']);

    // Shift Management (Admin)
    Route::get('/shifts', [ShiftController::class, 'index']);
    Route::get('/shifts/{shift}', [ShiftController::class, 'show']);
    Route::post('/shifts/start', [ShiftController::class, 'start']);
    Route::post('/shifts/close', [ShiftController::class, 'close']);

    // Dashboard Statistics (Admin)
    Route::get('/dashboard/stats', [DashboardController::class, 'index']);
});
