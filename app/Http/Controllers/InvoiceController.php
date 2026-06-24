<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Invoiceitem;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Shift;
use Illuminate\Http\Request;

class InvoiceController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'barber', 'appointment', 'invoiceitems.service']);

        if ($request->has('barber_id') && $request->barber_id) {
            $query->where('barber_id', $request->barber_id);
        }

        if ($request->has('customer_id') && $request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('shift_id') && $request->shift_id) {
            $query->where('shift_id', $request->shift_id);
        }

        if ($request->has('date') && $request->date) {
            $query->whereDate('created_at', $request->date);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'message' => 'تم عرض جميع الفواتير بنجاح',
            'status' => 200,
            'data' => InvoiceResource::collection($invoices)->response()->getData(true),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Find or create guest customer if not found
        $customer = null;
        if ($request->has('customer_id') && $request->customer_id) {
            $customer = Customer::find($request->customer_id);
        }

        if (! $customer) {
            do {
                $customer_id = rand(999999, 100000000);
            } while (Customer::where('id', $customer_id)->exists());

            $customer = Customer::create([
                'id' => $customer_id,
                'customer_name' => 'Unknown',
                'customer_phone' => '0000000000',
            ]);
        }

        // 2. Validate request parameters (without total_price)
        $request->validate([
            'barber_id' => 'required|exists:barbers,id',
            'appointment_id' => 'required|exists:appointments,id',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
        ]);

        $activeShift = Shift::where('shift_status', 'open')->first();
        if (! $activeShift) {
            return response()->json([
                'message' => 'لا يوجد شيفت مفتوح حالياً. يرجى فتح شيفت أولاً.',
                'status' => 400,
            ], 400);
        }

        // 3. Create invoice items and calculate total price
        $itemsData = [];
        $totalPrice = 0;

        foreach ($request->items as $item) {
            $service = Service::find($item['service_id']);
            $price = floatval($service->service_price);

            $totalPrice += $price;
            $itemsData[] = [
                'service_id' => $service->id,
                'price' => $price,
            ];
        }

        // Create the invoice
        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'barber_id' => $request->barber_id,
            'shift_id' => $activeShift->id,
            'appointment_id' => $request->appointment_id,
            'total_price' => $totalPrice,
        ]);

        // Create the invoice items
        foreach ($itemsData as $itemData) {
            Invoiceitem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $itemData['service_id'],
                'quantity' => 1,
                'price' => $itemData['price'],
            ]);
        }

        // Load relations
        $invoice->load(['customer', 'barber', 'appointment', 'invoiceitems.service']);

        return response()->json([
            'message' => 'تم انشاء فاتورة بنجاح',
            'status' => 201,
            'data' => new InvoiceResource($invoice),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $id = $request->id;
        $invoice = Invoice::find($id);
        if (! $invoice) {
            return response()->json([
                'message' => 'الفاتورة غير موجودة',
                'status' => 404,
            ], 404);
        }
        // Load relations
        $invoice->load(['customer', 'barber', 'appointment', 'invoiceitems.service']);

        return response()->json([
            'message' => 'تم عرض الفاتورة بنجاح',
            'status' => 200,
            'data' => new InvoiceResource($invoice),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::find($id);

        if (! $invoice) {
            return response()->json([
                'message' => 'الفاتورة غير موجودة',
                'status' => 404,
            ], 404);
        }

        $request->validate([
            'barber_id' => 'required|exists:barbers,id',
            'appointment_id' => 'required|exists:appointments,id',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
        ]);

        $activeShift = Shift::where('shift_status', 'open')->first();
        if (! $activeShift) {
            return response()->json([
                'message' => 'لا يوجد شيفت مفتوح حالياً. يرجى فتح شيفت أولاً.',
                'status' => 400,
            ], 400);
        }

        $itemsData = [];
        $totalPrice = 0;

        foreach ($request->items as $item) {
            $service = Service::find($item['service_id']);
            $price = floatval($service->service_price);

            $totalPrice += $price;
            $itemsData[] = [
                'service_id' => $service->id,
                'price' => $price,
            ];
        }

        $invoice->update([
            'barber_id' => $request->barber_id,
            'shift_id' => $activeShift->id,
            'appointment_id' => $request->appointment_id,
            'total_price' => $totalPrice,
        ]);

        // Delete old items and insert new ones
        $invoice->invoiceitems()->delete();
        foreach ($itemsData as $itemData) {
            Invoiceitem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $itemData['service_id'],
                'quantity' => 1,
                'price' => $itemData['price'],
            ]);
        }

        $invoice->load(['customer', 'barber', 'appointment', 'invoiceitems.service']);

        return response()->json([
            'message' => 'تم تعديل الفاتورة بنجاح',
            'status' => 200,
            'data' => new InvoiceResource($invoice),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $invoice = Invoice::find($id);

        if (! $invoice) {
            return response()->json([
                'message' => 'الفاتورة غير موجودة',
                'status' => 404,
            ], 404);
        }

        $invoice->delete();

        return response()->json([
            'message' => 'تم حذف الفاتورة بنجاح',
            'status' => 200,
        ]);
    }

    /**
     * Store a quick invoice without appointment.
     */
    public function quickStore(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string',
            'barber_id' => 'required|exists:barbers,id',
            'service_ids' => 'required_without:items|array',
            'service_ids.*' => 'exists:services,id',
            'items' => 'required_without:service_ids|array',
            'items.*.service_id' => 'exists:services,id',
        ]);

        $activeShift = Shift::where('shift_status', 'open')->first();
        if (! $activeShift) {
            return response()->json([
                'message' => 'لا يوجد شيفت مفتوح حالياً. يرجى فتح شيفت أولاً.',
                'status' => 400,
            ], 400);
        }

        // Find or create customer
        $customer = Customer::where('customer_phone', $request->customer_phone)->first();
        if (! $customer) {
            $customer = Customer::create([
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
            ]);
        } else {
            if ($request->customer_name && $customer->customer_name !== $request->customer_name) {
                $customer->update(['customer_name' => $request->customer_name]);
            }
        }

        // Gather service IDs
        $serviceIds = [];
        if ($request->has('service_ids')) {
            $serviceIds = $request->service_ids;
        } elseif ($request->has('items')) {
            $serviceIds = collect($request->items)->pluck('service_id')->toArray();
        }

        $itemsData = [];
        $totalPrice = 0;

        foreach ($serviceIds as $serviceId) {
            $service = Service::find($serviceId);
            if ($service) {
                $price = floatval($service->service_price);
                $totalPrice += $price;
                $itemsData[] = [
                    'service_id' => $service->id,
                    'price' => $price,
                ];
            }
        }

        // Create the invoice without appointment_id
        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'barber_id' => $request->barber_id,
            'shift_id' => $activeShift->id,
            'appointment_id' => null,
            'total_price' => $totalPrice,
        ]);

        // Create invoice items
        foreach ($itemsData as $itemData) {
            Invoiceitem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $itemData['service_id'],
                'quantity' => 1,
                'price' => $itemData['price'],
            ]);
        }

        // Load relations
        $invoice->load(['customer', 'barber', 'appointment', 'invoiceitems.service']);

        return response()->json([
            'message' => 'تم انشاء الفاتورة السريعة بنجاح',
            'status' => 201,
            'data' => new InvoiceResource($invoice),
        ], 201);
    }

    /**
     * View/Print receipt.
     */
    public function printReceipt($id)
    {
        $invoice = Invoice::with(['customer', 'barber', 'appointment', 'invoiceitems.service'])->find($id);

        if (!$invoice) {
            abort(404, 'الفاتورة غير موجودة');
        }

        return view('invoice_receipt', compact('invoice'));
    }
}

