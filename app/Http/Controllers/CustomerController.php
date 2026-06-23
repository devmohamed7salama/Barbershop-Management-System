<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    $customers = Customer::query()
        ->withCount(['invoices as visit_count'])
        ->withSum('invoices', 'total')
        ->orderBy('visit_count', 'desc')
        ->paginate(10);

    return response()->json([
        'message' => 'تم عرض جميع العملاء بنجاح',
        'status' => 200,
        'data' => $customers
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
        $data = [
            'message' => 'تم إضافة العميل بنجاح',
            'status' => 201,
            'customer' => $customer,
        ];

        return response()->json($data
        );
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
            $data = [
                'message' => 'تم عرض العميل بنجاح',
                'status' => 200,
                'customer' => $customer,
            ];

            return response()->json($data
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $id = $request->id;
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
            $data = [
                'message' => 'تم تعديل العميل بنجاح',
                'status' => 200,
                'customer' => $customer,
            ];

            return response()->json($data
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
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
