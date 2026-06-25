<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Appointment;
use App\Models\Shift;
use App\Models\Service;
use App\Models\Barber;
use Illuminate\Http\Request;

class DashboardController
{
    public function index()
    {
        // 1. Most Visiting Customers: Top 5 customers ordered by completed invoices count
        $mostVisitingCustomers = Customer::select('customers.id', 'customers.customer_name', 'customers.customer_phone')
            ->selectRaw('COUNT(invoices.id) as visits_count')
            ->join('invoices', 'customers.id', '=', 'invoices.customer_id')
            ->groupBy('customers.id', 'customers.customer_name', 'customers.customer_phone')
            ->orderBy('visits_count', 'desc')
            ->limit(5)
            ->get();

        // 2. Most Paying Customers: Top 5 customers ordered by sum of invoice total price
        $mostPayingCustomers = Customer::select('customers.id', 'customers.customer_name', 'customers.customer_phone')
            ->selectRaw('COALESCE(SUM(invoices.total_price), 0) as total_paid')
            ->join('invoices', 'customers.id', '=', 'invoices.customer_id')
            ->groupBy('customers.id', 'customers.customer_name', 'customers.customer_phone')
            ->orderBy('total_paid', 'desc')
            ->limit(5)
            ->get();

        // 3. Last 10 Haircuts (Completed Appointments)
        $lastAppointments = Appointment::with(['customer', 'services'])
            ->where('appointment_status', 'completed')
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'customer_name' => $appointment->customer?->customer_name,
                    'customer_phone' => $appointment->customer?->customer_phone,
                    'appointment_date' => $appointment->appointment_date,
                    'appointment_time' => $appointment->appointment_time,
                    'services' => $appointment->services->map(function ($s) {
                        return [
                            'id' => $s->id,
                            'service_name' => $s->service_name,
                            'service_price' => (float) $s->service_price,
                        ];
                    }),
                ];
            });

        // 4. Last 3 Shifts
        $lastShifts = Shift::orderBy('id', 'desc')
            ->limit(3)
            ->get();

        // 5. Most Requested Services: Top 5 services ordered by appearance count in completed appointments
        $mostRequestedServices = Service::select('services.id', 'services.service_name', 'services.service_price')
            ->selectRaw('COUNT(appointment_service.service_id) as demand_count')
            ->join('appointment_service', 'services.id', '=', 'appointment_service.service_id')
            ->join('appointments', 'appointments.id', '=', 'appointment_service.appointment_id')
            ->where('appointments.appointment_status', 'completed')
            ->groupBy('services.id', 'services.service_name', 'services.service_price')
            ->orderBy('demand_count', 'desc')
            ->limit(4)
            ->get();

        $stats = [
            [
                'name' => 'إجمالي العملاء',
                'value' => Customer::count(),
                'icon' => 'users',
            ],
            [
                'name' => 'إجمالي الخدمات',
                'value' => Service::count(),
                'icon' => 'scissors',
            ],
            [
                'name' => 'إجمالي الحلاقين',
                'value' => Barber::count(),
                'icon' => 'user-tie',
            ],
            [
                'name' => 'إجمالي رواتب الحلاقين',
                'value' => floatval(Barber::sum('salary')),
                'icon' => 'wallet',
            ],
        ];

        return response()->json([
            'message' => 'تم عرض إحصائيات لوحة التحكم بنجاح',
            'status' => 200,
            'data' => [
                'stats' => $stats,
                'most_visiting_customers' => $mostVisitingCustomers,
                'most_paying_customers' => $mostPayingCustomers,
                'recent_haircuts' => $lastAppointments,
                'recent_shifts' => $lastShifts,
                'popular_services' => $mostRequestedServices,
            ],
        ]);
    }
}
