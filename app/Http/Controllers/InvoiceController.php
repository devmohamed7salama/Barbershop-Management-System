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
    public function index()
    {
        $invoices = Invoice::with(['customer', 'barber', 'appointment', 'invoiceitems.service'])->paginate(10);

        return response()->json([
            'message' => 'تم عرض جميع الفواتير بنجاح',
            'status' => 200,
            'data' => InvoiceResource::collection($invoices),
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

        $data = [
            'message' => 'تم انشاء فاتورة بنجاح',
            'status' => 201,
            'invoice' => new InvoiceResource($invoice),
        ];

        return response()->json($data, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $id = $request->id;
        $invoice=Invoice::find($id);
        if (!$invoice) {
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
}
