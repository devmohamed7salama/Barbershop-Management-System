<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;


class ShiftController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shifts = Shift::paginate(10);

        return response()->json([
            'message' => 'تم عرض الشفتات بنجاح',
            'status' => 200,
            'data' => $shifts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function start(Request $request)
    {
        if (Shift::where('shift_status', 'open')->exists()) {
            return response()->json([
                'message' => 'تم فتح الشفت بالفعل',
                'status' => 400,
            ], 400);
        }

        $shift = Shift::create([
            'start_time' => now()->toTimeString(),
            'shift_status' => 'open',
        ]);

        return response()->json([
            'message' => 'تم إنشاء الشفت بنجاح',
            'status' => 201,
            'data' => $shift,
        ]);
    }
    //    'start_time',
//         'end_time',
//         'total_cash',
//         'total_revenue',
//         'total_orders',
//         'shift_status',

    /**
     * Close the shift.
     */
    public function close(Request $request)
    {
        $request->validate([
            'shift_id' => 'required',
        ]);

        $shift = Shift::find($request->shift_id);
        if (!$shift) {
            return response()->json([
                'message' => 'الشيفت غير موجود',
                'status' => 404,
            ], 404);
        }

        $totalOrders = $shift->invoices()->count();
        $totalRevenue = floatval($shift->invoices()->sum('total_price'));

        $shift->update([
            'end_time' => now()->toTimeString(),
            'shift_status' => 'closed',
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
        ]);

        return response()->json([
            'message' => 'تم إغلاق الشفت بنجاح',
            'status' => 200,
            'data' => $shift,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Shift $shift)
    {
        $id = $shift->id;
        $shift = Shift::find($id);

        return response()->json([
            'message' => 'تم عرض الشفت بنجاح',
            'status' => 200,
            'data' => $shift,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shift $shift)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shift $shift)
    {
        //
    }
}
