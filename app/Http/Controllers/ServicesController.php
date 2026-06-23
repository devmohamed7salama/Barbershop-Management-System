<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Http\Resources\ServiceResource;
use Illuminate\Http\Request;

class ServicesController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Service::withCount(['appointments as demand_count' => function ($query) {
            $query->where('appointment_status', 'completed');
        }]);

        // If user is not an admin, filter by service_status = 'published'
        $user = $request->user('sanctum');
        if (!$user || $user->role !== 'admin') {
            $query->where('service_status', 'published');
        }

        // Search by service name
        if ($request->has('search') && $request->search) {
            $query->where('service_name', 'like', '%' . $request->search . '%');
        }

        $services = $query->paginate(10);

        return response()->json([
            'message' => 'تم عرض جميع الخدمات بنجاح',
            'status' => 200,
            'data' => ServiceResource::collection($services)->response()->getData(true),
        ]);
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
            'service_status' => 'nullable|in:published,hidden',
        ]);

        $path = '';
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
        $service->service_status = $request->service_status ?? 'published';
        $service->save();

        return response()->json([
            'message' => 'تم إضافة الخدمة بنجاح',
            'status' => 201,
            'data' => new ServiceResource($service),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $service = Service::withCount(['appointments as demand_count' => function ($query) {
            $query->where('appointment_status', 'completed');
        }])->find($id);

        if ($service == null) {
            return response()->json([
                'message' => 'الخدمة غير موجودة',
                'status' => 205,
            ]);
        }

        return response()->json([
            'message' => 'تم عرض الخدمة بنجاح',
            'status' => 200,
            'data' => new ServiceResource($service),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id = null)
    {
        $id = $id ?? $request->id;
        $request->validate([
            'service_name' => 'required',
            'service_description' => 'required',
            'service_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'service_price' => 'required',
            'service_duration' => 'required|min:1',
            'service_status' => 'nullable|in:published,hidden',
        ]);

        $service = Service::find($id);
        if ($service == null) {
            return response()->json([
                'message' => 'الخدمة غير موجودة',
                'status' => 205,
            ]);
        }

        $path = $request->service_image;
        if ($request->hasFile('service_image')) {
            $image = $request->file('service_image');
            $imageName = rand(1, 100).time().'.'.$image->getClientOriginalExtension();
            $path = $image->move(public_path('images'), $imageName);
        }

        if ($request->service_image == null) {
            $path = $service->service_image;
        }

        $service->service_name = $request->service_name;
        $service->service_description = $request->service_description;
        $service->service_image = $path;
        $service->service_price = $request->service_price;
        $service->service_duration = $request->service_duration;
        if ($request->has('service_status')) {
            $service->service_status = $request->service_status;
        }
        $service->update();

        $service->loadCount(['appointments as demand_count' => function ($query) {
            $query->where('appointment_status', 'completed');
        }]);

        return response()->json([
            'message' => 'تم تعديل الخدمة بنجاح',
            'status' => 200,
            'data' => new ServiceResource($service),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = Service::find($id);
        if ($service == null) {
            return response()->json([
                'message' => 'الخدمة غير موجودة',
                'status' => 205,
            ]);
        }

        $service->delete();
        return response()->json([
            'message' => 'تم حذف الخدمة بنجاح',
            'status' => 200,
        ]);
    }
}
