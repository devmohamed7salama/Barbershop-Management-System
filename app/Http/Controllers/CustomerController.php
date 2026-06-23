<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Resources\CustomerResource;
use Illuminate\Http\Request;

class CustomerController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::query()
            ->withCount(['invoices as visit_count'])
            ->withSum('invoices as total_spent', 'total_price');

        // Search by name or phone
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $request->search . '%');
            });
        }

        $customers = $query->orderBy('visit_count', 'desc')->paginate(10);

        return response()->json([
            'message' => 'تم عرض جميع العملاء بنجاح',
            'status' => 200,
            'data' => CustomerResource::collection($customers)->response()->getData(true)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required',
            'customer_phone' => 'required|unique:customers,customer_phone|max:11|min:11',
        ]);
        $customer = new Customer;
        $customer->customer_name = $request->customer_name;
        $customer->customer_phone = $request->customer_phone;
        $customer->save();

        return response()->json([
            'message' => 'تم إضافة العميل بنجاح',
            'status' => 201,
            'data' => new CustomerResource($customer),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $id = $request->id;
        $customer = Customer::find($id);
        if ($customer == null) {
            $data = [
                'message' => 'العميل غير موجود',
                'status' => 205,
            ];

            return response()->json($data
            );
        } else {
            return response()->json([
                'message' => 'تم عرض العميل بنجاح',
                'status' => 200,
                'data' => new CustomerResource($customer),
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id = null)
    {
        $id = $id ?? $request->id;
        $request->validate([
            'customer_name' => 'required',
            'customer_phone' => 'required',
        ]);
        $customer = Customer::find($id);
        if ($customer == null) {
            $data = [
                'message' => 'العميل غير موجود',
                'status' => 205,
            ];

            return response()->json($data
            );
        } else {
            $customer->customer_name = $request->customer_name;
            $customer->customer_phone = $request->customer_phone;
            $customer->update();

            return response()->json([
                'message' => 'تم تعديل العميل بنجاح',
                'status' => 200,
                'data' => new CustomerResource($customer),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id = null)
    {
        $id = $id ?? $request->id;
        $customer = Customer::find($id);
        if ($customer == null) {
            $data = [
                'message' => 'العميل غير موجود',
                'status' => 205,
            ];

            return response()->json($data
            );
        } else {
            $customer->delete();
            $data = [
                'message' => 'تم حذف العميل بنجاح',
                'status' => 200,
            ];

            return response()->json($data
            );
        }
    }
}
