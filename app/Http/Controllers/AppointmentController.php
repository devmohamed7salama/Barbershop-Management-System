<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Invoice;
use App\Models\Invoiceitem;
use App\Models\Shift;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\AppointmentResource;
use Illuminate\Http\Request;

class AppointmentController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['customer', 'services']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('appointment_status', $request->status);
        }

        // Filter by date
        if ($request->has('date') && $request->date) {
            $query->where('appointment_date', $request->date);
        }

        // Search by customer name or phone
        if ($request->has('search') && $request->search) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $request->search . '%');
            });
        }

        $appointments = $query->paginate(10);

        return response()->json([
            'message' => 'تم عرض جميع المواعيد بنجاح',
            'status' => 200,
            'data' => AppointmentResource::collection($appointments)->response()->getData(true),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_phone' => 'required',
            'customer_name' => 'required_without:customer_id',
            'appointment_date' => 'required',
            'appointment_time' => 'required',
            'service_ids' => 'required',
            'source' => 'nullable|in:online,offline',
            'appointment_status' => 'nullable|in:pending,completed,cancelled',
            'appointment_notes' => 'nullable|string',
        ]);

        // Find or create customer by phone
        $serviceIds = is_array($request->service_ids) ? $request->service_ids : [$request->service_ids];
        $user = $request->user('sanctum');
        if (!$user || $user->role !== 'admin') {
            $invalidServicesCount = Service::whereIn('id', $serviceIds)
                ->where('service_status', 'hidden')
                ->count();
            if ($invalidServicesCount > 0) {
                return response()->json([
                    'message' => 'بعض الخدمات المحددة غير متاحة للحجز حالياً.',
                    'status' => 400
                ], 400);
            }
        }

        $customer = Customer::where('customer_phone', $request->customer_phone)->first();
        if (! $customer) {
            $customer = Customer::create([
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
            ]);
        }

        // Create the appointment
        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'source' => $request->source ?? 'online',
            'appointment_status' => $request->appointment_status ?? 'pending',
            'appointment_notes' => $request->appointment_notes,
        ]);

        // Attach services
        $appointment->services()->attach($serviceIds);

        // Calculate total price
        $totalPrice = Service::whereIn('id', $serviceIds)->sum('service_price');

        // Load relations
        $appointment->load(['customer', 'services']);
        $appointment->total_price = floatval($totalPrice);

        return response()->json([
            'message' => 'تم إنشاء الموعد بنجاح',
            'status' => 201,
            'data' => new AppointmentResource($appointment),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $appointment = Appointment::with(['customer', 'services'])->find($id);

        if (! $appointment) {
            return response()->json([
                'message' => 'الموعد غير موجود',
                'status' => 404,
            ], 404);
        }

        return response()->json([
            'message' => 'تم عرض الموعد بنجاح',
            'status' => 200,
            'data' => new AppointmentResource($appointment),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (! $appointment) {
            return response()->json([
                'message' => 'الموعد غير موجود',
                'status' => 404,
            ], 404);
        }

        if ($appointment->appointment_status === 'completed') {
            return response()->json([
                'message' => 'لا يمكن تعديل موعد مكتمل بالفعل',
                'status' => 400,
            ], 400);
        }

        $request->validate([
            'customer_phone' => 'nullable',
            'customer_name' => 'nullable',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'service_ids' => 'required',
            'source' => 'nullable|in:online,offline',
            'appointment_status' => 'nullable|in:pending,completed,cancelled',
            'appointment_notes' => 'nullable|string',
        ]);

        if ($request->has('customer_phone')) {
            $customer = Customer::where('customer_phone', $request->customer_phone)->first();
            if (! $customer && $request->has('customer_name')) {
                $customer = Customer::create([
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                ]);
            }
            if ($customer) {
                $appointment->customer_id = $customer->id;
            }
        }

        $appointment->appointment_date = $request->appointment_date;
        $appointment->appointment_time = $request->appointment_time;
        if ($request->has('source')) {
            $appointment->source = $request->source;
        }
        if ($request->has('appointment_status')) {
            $appointment->appointment_status = $request->appointment_status;
        }
        if ($request->has('appointment_notes')) {
            $appointment->appointment_notes = $request->appointment_notes;
        }
        $appointment->save();

        $serviceIds = is_array($request->service_ids) ? $request->service_ids : [$request->service_ids];
        $appointment->services()->sync($serviceIds);

        $totalPrice = Service::whereIn('id', $serviceIds)->sum('service_price');

        $appointment->load(['customer', 'services']);
        $appointment->total_price = floatval($totalPrice);

        return response()->json([
            'message' => 'تم تعديل الموعد بنجاح',
            'status' => 200,
            'data' => new AppointmentResource($appointment),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $appointment = Appointment::find($id);

        if (! $appointment) {
            return response()->json([
                'message' => 'الموعد غير موجود',
                'status' => 404,
            ], 404);
        }

        $appointment->delete();

        return response()->json([
            'message' => 'تم حذف الموعد بنجاح',
            'status' => 200,
        ]);
    }

    /**
     * Update the status of the specified resource.
     */
    public function updateStatus(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (! $appointment) {
            return response()->json([
                'message' => 'الموعد غير موجود',
                'status' => 205,
            ], 205);
        }

        if ($appointment->appointment_status === 'completed') {
            return response()->json([
                'message' => 'لا يمكن تعديل موعد مكتمل بالفعل',
                'status' => 400,
            ], 400);
        }

        $request->validate([
            'appointment_status' => 'required|in:pending,completed,cancelled',
            'barber_id' => 'required_if:appointment_status,completed|exists:barbers,id',
        ]);

        $activeShift = null;
        if ($request->appointment_status === 'completed') {
            $activeShift = Shift::where('shift_status', 'open')->first();
            if (! $activeShift) {
                return response()->json([
                    'message' => 'لا يوجد شيفت مفتوح حالياً. يرجى فتح شيفت أولاً.',
                    'status' => 400,
                ], 400);
            }
        }

        $appointment->appointment_status = $request->appointment_status;
        $appointment->save();

        if ($appointment->appointment_status === 'completed') {
            // Calculate total price from appointment services
            $totalPrice = $appointment->services()->sum('service_price');

            // Create the invoice
            $invoice = Invoice::create([
                'customer_id' => $appointment->customer_id,
                'barber_id' => $request->barber_id,
                'shift_id' => $activeShift->id,
                'appointment_id' => $appointment->id,
                'total_price' => $totalPrice,
            ]);

            // Create invoice items for each service in the appointment
            foreach ($appointment->services as $service) {
                Invoiceitem::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => $service->id,
                    'quantity' => 1,
                    'price' => $service->service_price,
                ]);
            }
        }

        $invoiceData = null;
        if ($appointment->appointment_status === 'completed') {
            $invoice = Invoice::where('appointment_id', $appointment->id)->first();
            if ($invoice) {
                $invoice->load(['customer', 'barber', 'appointment', 'invoiceitems.service']);
                $invoiceData = new InvoiceResource($invoice);
            }
        }

        $appointment->load(['customer', 'services']);

        return response()->json([
            'message' => 'تم تغيير حالة الموعد بنجاح',
            'status' => 200,
            'data' => [
                'appointment_status' => $appointment->appointment_status,
                'appointment' => new AppointmentResource($appointment),
                'invoice' => $invoiceData,
            ],
        ]);
    }
}
