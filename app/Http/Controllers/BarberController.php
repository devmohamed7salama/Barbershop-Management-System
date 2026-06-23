<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Http\Resources\BarberResource;
use Illuminate\Http\Request;

class BarberController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Barber::query();

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('barber_status', $request->status);
        }

        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('barber_name', 'like', '%' . $request->search . '%');
        }

        $barbers = $query->paginate(10);

        return response()->json([
            'message' => 'تم عرض جميع الحلاقين بنجاح',
            'status' => 200,
            'data' => BarberResource::collection($barbers)->response()->getData(true),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'barber_name' => 'required',
            'barber_phone' => 'required|unique:barbers|max:11|min:11',
            'barber_nid' => 'required|unique:barbers',
            'salary' => 'required',
        ]);

        if ($request->barber_status == null) {
            $request->barber_status = 'available';
        }

        $barber = new Barber;
        $barber->barber_name = $request->barber_name;
        $barber->barber_phone = $request->barber_phone;
        $barber->barber_nid = $request->barber_nid;
        $barber->salary = $request->salary;
        $barber->barber_status = $request->barber_status;
        $barber->save();

        return response()->json([
            'message' => 'تم إضافة حلاق بنجاح',
            'status' => 201,
            'data' => new BarberResource($barber),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $id = $request->id;
        $barber = Barber::find($id);
        if ($barber == null) {
            return response()->json([
                'message' => 'الحلاق غير موجود',
                'status' => 205,
            ]);
        }

        return response()->json([
            'message' => 'تم عرض الحلاق بنجاح',
            'status' => 200,
            'data' => new BarberResource($barber),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id = null)
    {
        $id = $id ?? $request->id;
        $barber = Barber::find($id);
        if ($barber == null) {
            return response()->json([
                'message' => 'الحلاق غير موجود',
                'status' => 205,
            ]);
        }

        $request->validate([
            'barber_name' => 'required',
            'barber_phone' => 'unique:barbers,barber_phone,'.$id.',id|max:11|min:11',
            'barber_nid' => 'unique:barbers,barber_nid,'.$id.',id',
            'salary' => 'required',
        ]);

        if ($request->barber_status == null) {
            $request->barber_status = 'available';
        }

        $barber->barber_name = $request->barber_name;
        $barber->barber_phone = $request->barber_phone;
        $barber->barber_nid = $request->barber_nid;
        $barber->salary = $request->salary;
        $barber->barber_status = $request->barber_status;
        $barber->update();

        return response()->json([
            'message' => 'تم تعديل الحلاق بنجاح',
            'status' => 200,
            'data' => new BarberResource($barber),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id = null)
    {
        $id = $id ?? $request->id;
        $barber = Barber::find($id);
        if ($barber == null) {
            return response()->json([
                'message' => 'الحلاق غير موجود',
                'status' => 205,
            ]);
        }

        $barber->delete();
        return response()->json([
            'message' => 'تم حذف الحلاق بنجاح',
            'status' => 200,
        ]);
    }
}
