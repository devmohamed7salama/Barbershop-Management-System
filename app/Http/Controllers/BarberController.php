<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use Illuminate\Http\Request;

class BarberController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $barbers = Barber::paginate(10);
        $data = [
            'message' => 'تم عرض جميع الحلاقين بنجاح',
            'status' => 200,
            'barbers' => $barbers,
        ];

        return response()->json($data);
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
        $data = [
            'message' => 'تم إضافة حلاق بنجاح',
            'status' => 201,
            'barber' => $barber,
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
        $barber = Barber::find($id);
        if ($barber == null) {
            $data = [
                'message' => 'الحلاق غير موجود',
                'status' => 205,
            ];

            return response()->json($data
            );
        } else {
            $data = [
                'message' => 'تم عرض الحلاق بنجاح',
                'status' => 200,
                'barber' => $barber,
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
        $barber = Barber::find($id);
        if ($barber == null) {
            $data = [
                'message' => 'الحلاق غير موجود',
                'status' => 205,
            ];

            return response()->json($data
            );
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
        $data = [
            'message' => 'تم تعديل الحلاق بنجاح',
            'status' => 200,
            'barber' => $barber,
        ];

        return response()->json($data
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->id;
        $barber = Barber::find($id);
        if ($barber == null) {
            $data = [
                'message' => 'الحلاق غير موجود',
                'status' => 205,
            ];

            return response()->json($data
            );
        } else {
            $barber->delete();
            $data = [
                'message' => 'تم حذف الحلاق بنجاح',
                'status' => 200,
            ];

            return response()->json($data
            );
        }
    }
}
