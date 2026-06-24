<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Shift;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Invoiceitem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing tables to ensure clean seeding
        Invoiceitem::truncate();
        Invoice::truncate();
        DB::table('appointment_service')->truncate();
        Appointment::truncate();
        Customer::truncate();
        Barber::truncate();
        Shift::truncate();
        Service::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Create Barbers
        $barbersData = [
            ['name' => 'أحمد علي', 'phone' => '01012345678', 'nid' => '29501010101234', 'salary' => 5000, 'status' => 'available'],
            ['name' => 'محمد حسن', 'phone' => '01123456789', 'nid' => '29602020201234', 'salary' => 6000, 'status' => 'available'],
            ['name' => 'مصطفى محمود', 'phone' => '01234567890', 'nid' => '29703030301234', 'salary' => 4500, 'status' => 'available'],
            ['name' => 'كريم إبراهيم', 'phone' => '01545678901', 'nid' => '29804040401234', 'salary' => 5500, 'status' => 'available'],
        ];

        $barbers = [];
        foreach ($barbersData as $data) {
            $barbers[] = Barber::create([
                'barber_name' => $data['name'],
                'barber_phone' => $data['phone'],
                'barber_nid' => $data['nid'],
                'salary' => $data['salary'],
                'barber_status' => $data['status'],
            ]);
        }

        // 2. Create Customers
        $customersData = [
            ['name' => 'خالد عبد الرحمن', 'phone' => '01098765432'],
            ['name' => 'ياسر فاروق', 'phone' => '01198765432'],
            ['name' => 'طارق حامد', 'phone' => '01298765432'],
            ['name' => 'عمرو دياب', 'phone' => '01598765432'],
            ['name' => 'هاني شاكر', 'phone' => '01055554444'],
            ['name' => 'عادل إمام', 'phone' => '01144443333'],
            ['name' => 'حسين فهمي', 'phone' => '01233332222'],
            ['name' => 'كريم عبد العزيز', 'phone' => '01522221111'],
            ['name' => 'أحمد عز', 'phone' => '01011119999'],
            ['name' => 'محمد رمضان', 'phone' => '01122228888'],
            ['name' => 'تامر حسني', 'phone' => '01233337777'],
            ['name' => 'رامز جلال', 'phone' => '01544446666'],
            ['name' => 'ماجد الكدواني', 'phone' => '01055556666'],
            ['name' => 'شريف منير', 'phone' => '01166667777'],
            ['name' => 'مصطفى شعبان', 'phone' => '01277778888'],
        ];

        $customers = [];
        foreach ($customersData as $data) {
            $customers[] = Customer::create([
                'customer_name' => $data['name'],
                'customer_phone' => $data['phone'],
            ]);
        }

        // 3. Create Services
        $servicesData = [
            ['name' => 'قص شعر كلاسيك', 'desc' => 'قص وتصفيف الشعر بالطريقة التقليدية الرجالية الفاخرة.', 'price' => 150, 'duration' => 30],
            ['name' => 'حلاقة لحية وتحديد بالليزر', 'desc' => 'حلاقة الدقن مع تحديد ناعم بالليزر وجلسة بخار.', 'price' => 100, 'duration' => 20],
            ['name' => 'تنظيف بشرة متكامل (سكرب + ماسك)', 'desc' => 'ماسك صنفرة وتنظيف بشرة بجهاز البخار وإزالة البثور السوداء.', 'price' => 200, 'duration' => 45],
            ['name' => 'قص وتلوين شعر / صبغة', 'desc' => 'تغيير لون الشعر أو تغطية الشيب بصبغات إيطالية فاخرة خالية من الأمونيا.', 'price' => 350, 'duration' => 60],
            ['name' => 'بروتين معالج وفرد شعر لشعر ناعم', 'desc' => 'جلسة معالجة الشعر وتنعيمه بالبروتين الطبيعي المغذي والمثبت بالحرارة.', 'price' => 800, 'duration' => 120],
            ['name' => 'استشوار وكريم تثبيت', 'desc' => 'غسيل شعر واستشوار مع تطبيق كريم مغذي لتثبيت وتألق الشعر.', 'price' => 80, 'duration' => 20],
        ];

        $services = [];
        foreach ($servicesData as $data) {
            $services[] = Service::create([
                'service_name' => $data['name'],
                'service_description' => $data['desc'],
                'service_image' => 'images/dummy.png',
                'service_price' => $data['price'],
                'service_duration' => $data['duration'],
                'service_status' => 'published',
            ]);
        }

        // 4. Create Shifts
        $shift1 = Shift::create([
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'total_cash' => 2500,
            'total_revenue' => 2500,
            'total_orders' => 12,
            'shift_status' => 'closed',
            'created_at' => now()->subDays(2),
        ]);

        $shift2 = Shift::create([
            'start_time' => '17:00:00',
            'end_time' => '23:59:00',
            'total_cash' => 3800,
            'total_revenue' => 3800,
            'total_orders' => 15,
            'shift_status' => 'closed',
            'created_at' => now()->subDay(),
        ]);

        $activeShift = Shift::create([
            'start_time' => '09:00:00',
            'shift_status' => 'open',
            'created_at' => now(),
        ]);

        // 5. Create Appointments & Invoices
        $statuses = ['completed', 'pending', 'cancelled'];
        $sources = ['online', 'offline'];

        for ($i = 1; $i <= 35; $i++) {
            $customer = $customers[array_rand($customers)];
            $barber = $barbers[array_rand($barbers)];
            
            $daysAgo = rand(0, 2);
            $date = now()->subDays($daysAgo)->format('Y-m-d');
            
            $hour = rand(10, 22);
            $minute = array_rand(['00', '15', '30', '45']);
            $time = "$hour:$minute:00";
            
            if ($i <= 25) {
                $status = 'completed';
            } elseif ($i <= 32) {
                $status = 'pending';
            } else {
                $status = 'cancelled';
            }

            $appt = Appointment::create([
                'customer_id' => $customer->id,
                'appointment_date' => $date,
                'appointment_time' => $time,
                'source' => $sources[array_rand($sources)],
                'appointment_status' => $status,
                'appointment_notes' => rand(0, 1) ? 'يرجى التركيز على غسيل الشعر بالبلسم.' : null,
                'created_at' => now()->subDays($daysAgo)->subHours(rand(1, 10)),
            ]);

            // Attach 1 to 3 random services
            $selectedIndices = (array) array_rand($services, rand(1, 3));
            $serviceIds = [];
            foreach ($selectedIndices as $index) {
                $serviceIds[] = $services[$index]->id;
            }
            $appt->services()->attach($serviceIds);

            // If completed, create an invoice
            if ($status === 'completed') {
                $totalPrice = 0;
                $itemsData = [];
                foreach ($serviceIds as $id) {
                    $srv = Service::find($id);
                    $totalPrice += $srv->service_price;
                    $itemsData[] = $srv;
                }

                $shift = $activeShift;
                if ($daysAgo == 2) {
                    $shift = $shift1;
                } elseif ($daysAgo == 1) {
                    $shift = $shift2;
                }

                $invoice = Invoice::create([
                    'customer_id' => $customer->id,
                    'barber_id' => $barber->id,
                    'shift_id' => $shift->id,
                    'appointment_id' => $appt->id,
                    'total_price' => $totalPrice,
                    'created_at' => $appt->created_at,
                ]);

                foreach ($itemsData as $srv) {
                    Invoiceitem::create([
                        'invoice_id' => $invoice->id,
                        'service_id' => $srv->id,
                        'quantity' => 1,
                        'price' => $srv->service_price,
                        'created_at' => $appt->created_at,
                    ]);
                }
            }
        }
    }
}
