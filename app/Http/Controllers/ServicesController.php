<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServicesController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::withCount(['appointments as demand_count' => function ($query) {
            $query->where('appointment_status', 'completed');
        }])->paginate(10);

        $data = [
            'message' => 'تم عرض جميع الخدمات بنجاح',
            'status' => 200,
            'services' => $services,
        ];

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_name' => 'required',
            'service_description' => 'required',
            'service_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'service_price' => 'required',
            'service_duration' => 'required|min:1',
        ]);
        if ($request->hasFile('service_image')) {
            $image = $request->file('service_image');
            $imageName = rand(1, 100).time().'.'.$image->getClientOriginalExtension();
            $path = $image->move(public_path('images'), $imageName);
        }
        $service = new Service;
        $service->service_name = $request->service_name;
        $service->service_description = $request->service_description;
        $service->service_image = $path;
        $service->service_price = $request->service_price;
        $service->service_duration = $request->service_duration;
        $service->save();
        $data = [
            'message' => 'تم إضافة الخدمة بنجاح',
            'status' => 201,
            'service' => $service,
        ];

        return response()->json($data
        );
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $service = Service::find($id);
        if ($service == null) {
            $data = [
                'message' => 'الخدمة غير موجودة',
                'status' => 205,
            ];

            return response()->json($data
            );
        } else {

            $data = [
                'message' => 'تم عرض الخدمة بنجاح',
                'status' => 200,
                'service' => $service,
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
            'service_name' => 'required',
            'service_description' => 'required',
            'service_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'service_price' => 'required',
            'service_duration' => 'required|min:1',
        ]);

        $path = $request->service_image;
        if ($request->hasFile('service_image')) {
            $image = $request->file('service_image');
            $imageName = rand(1, 100).time().'.'.$image->getClientOriginalExtension();
            $path = $image->move(public_path('images'), $imageName);
        }

        $service = Service::find($id);
        if ($request->service_image == null) {
            $path = $service->service_image;

        }
        $service->service_name = $request->service_name;
        $service->service_description = $request->service_description;
        $service->service_image = $path;
        $service->service_price = $request->service_price;
        $service->service_duration = $request->service_duration;
        $service->update();

        $data = [
            'message' => 'تم تعديل الخدمة بنجاح',
            'status' => 200,
            'service' => $service,
        ];

        return response()->json($data
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = Service::find($id);
        if ($service == null) {
            $data = [
                'message' => 'الخدمة غير موجودة',
                'status' => 205,
            ];

            return response()->json($data
            );
        } else {
            $service->delete();
            $data = [
                'message' => 'تم حذف الخدمة بنجاح',
                'status' => 200,
            ];

            return response()->json($data
            );
        }
    }
}
