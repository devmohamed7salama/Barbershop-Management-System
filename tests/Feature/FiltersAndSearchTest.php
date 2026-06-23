<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Shift;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FiltersAndSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $admin = \App\Models\User::create([
            'user_name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        \Laravel\Sanctum\Sanctum::actingAs($admin);
    }

    public function test_appointment_filters_and_search()
    {
        $customer1 = Customer::create(['customer_name' => 'Mohamed Salama', 'customer_phone' => '01011111111']);
        $customer2 = Customer::create(['customer_name' => 'Ahmed Ali', 'customer_phone' => '01022222222']);

        $appointment1 = Appointment::create([
            'customer_id' => $customer1->id,
            'appointment_date' => '2026-06-25',
            'appointment_time' => '14:30:00',
            'source' => 'online',
            'appointment_status' => 'pending',
        ]);

        $appointment2 = Appointment::create([
            'customer_id' => $customer2->id,
            'appointment_date' => '2026-06-26',
            'appointment_time' => '15:30:00',
            'source' => 'offline',
            'appointment_status' => 'completed',
        ]);

        // 1. Filter by status
        $response = $this->getJson('/api/appoiments?status=completed');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertJsonPath('data.data.0.id', $appointment2->id);

        // 2. Filter by date
        $response = $this->getJson('/api/appoiments?date=2026-06-25');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertJsonPath('data.data.0.id', $appointment1->id);

        // 3. Search by name
        $response = $this->getJson('/api/appoiments?search=Salama');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertJsonPath('data.data.0.customer.customer_name', 'Mohamed Salama');

        // 4. Search by phone
        $response = $this->getJson('/api/appoiments?search=01022222222');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertJsonPath('data.data.0.customer.customer_name', 'Ahmed Ali');
    }

    public function test_customer_search()
    {
        Customer::create(['customer_name' => 'Mohamed Salama', 'customer_phone' => '01011111111']);
        Customer::create(['customer_name' => 'Ahmed Ali', 'customer_phone' => '01022222222']);

        // Search by name
        $response = $this->getJson('/api/customers?search=Mohamed');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertJsonPath('data.data.0.customer_name', 'Mohamed Salama');

        // Search by phone
        $response = $this->getJson('/api/customers?search=01022222222');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertJsonPath('data.data.0.customer_name', 'Ahmed Ali');
    }

    public function test_service_search()
    {
        Service::create([
            'service_name' => 'Haircut Premium',
            'service_description' => 'Great haircut',
            'service_image' => 'haircut.jpg',
            'service_price' => 100,
            'service_duration' => 30,
        ]);
        Service::create([
            'service_name' => 'Shaving',
            'service_description' => 'Great shaving',
            'service_image' => 'shave.jpg',
            'service_price' => 50,
            'service_duration' => 20,
        ]);

        $response = $this->getJson('/api/services?search=Premium');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertJsonPath('data.data.0.service_name', 'Haircut Premium');
    }

    public function test_barber_filters_and_search()
    {
        Barber::create([
            'barber_name' => 'Joe Shaver',
            'barber_phone' => '01011111111',
            'barber_nid' => '12345678901234',
            'salary' => 500.00,
            'barber_status' => 'available',
        ]);
        Barber::create([
            'barber_name' => 'Jack Clipper',
            'barber_phone' => '01022222222',
            'barber_nid' => '12345678901235',
            'salary' => 600.00,
            'barber_status' => 'unavailable',
        ]);

        // 1. Filter by status
        $response = $this->getJson('/api/barbers?status=unavailable');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertJsonPath('data.data.0.barber_name', 'Jack Clipper');

        // 2. Search by name
        $response = $this->getJson('/api/barbers?search=Joe');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertJsonPath('data.data.0.barber_name', 'Joe Shaver');
    }

    public function test_invoice_filters()
    {
        $customer1 = Customer::create(['customer_name' => 'Mohamed Salama', 'customer_phone' => '01011111111']);
        $customer2 = Customer::create(['customer_name' => 'Ahmed Ali', 'customer_phone' => '01022222222']);

        $barber1 = Barber::create([
            'barber_name' => 'Joe Shaver',
            'barber_phone' => '01011111111',
            'barber_nid' => '12345678901234',
            'salary' => 500.00,
            'barber_status' => 'available',
        ]);
        $barber2 = Barber::create([
            'barber_name' => 'Jack Clipper',
            'barber_phone' => '01022222222',
            'barber_nid' => '12345678901235',
            'salary' => 600.00,
            'barber_status' => 'available',
        ]);

        $shift1 = Shift::create(['start_time' => '08:00:00', 'shift_status' => 'open']);
        $shift2 = Shift::create(['start_time' => '16:00:00', 'shift_status' => 'closed']);

        $appointment1 = Appointment::create([
            'customer_id' => $customer1->id,
            'appointment_date' => '2026-06-25',
            'appointment_time' => '10:00:00',
            'source' => 'online',
        ]);
        $appointment2 = Appointment::create([
            'customer_id' => $customer2->id,
            'appointment_date' => '2026-06-25',
            'appointment_time' => '11:00:00',
            'source' => 'online',
        ]);

        $invoice1 = Invoice::create([
            'customer_id' => $customer1->id,
            'barber_id' => $barber1->id,
            'shift_id' => $shift1->id,
            'appointment_id' => $appointment1->id,
            'total_price' => 50.00,
        ]);
        // Modify created_at to yesterday for testing date filter
        $invoice1->created_at = now()->subDay();
        $invoice1->save();

        $invoice2 = Invoice::create([
            'customer_id' => $customer2->id,
            'barber_id' => $barber2->id,
            'shift_id' => $shift2->id,
            'appointment_id' => $appointment2->id,
            'total_price' => 100.00,
        ]);

        // 1. Filter by barber_id
        $response = $this->getJson('/api/invoices?barber_id=' . $barber2->id);
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertJsonPath('data.data.0.id', $invoice2->id);

        // 2. Filter by customer_id
        $response = $this->getJson('/api/invoices?customer_id=' . $customer1->id);
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertJsonPath('data.data.0.id', $invoice1->id);

        // 3. Filter by shift_id
        $response = $this->getJson('/api/invoices?shift_id=' . $shift1->id);
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertJsonPath('data.data.0.id', $invoice1->id);

        // 4. Filter by date (today)
        $todayStr = now()->format('Y-m-d');
        $response = $this->getJson('/api/invoices?date=' . $todayStr);
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertJsonPath('data.data.0.id', $invoice2->id);
    }

    public function test_service_status_gating()
    {
        // 1. Create a published service and a hidden service
        $publishedService = Service::create([
            'service_name' => 'Haircut Published',
            'service_description' => 'Available to all',
            'service_image' => 'haircut.jpg',
            'service_price' => 50,
            'service_duration' => 30,
            'service_status' => 'published',
        ]);

        $hiddenService = Service::create([
            'service_name' => 'Haircut Hidden',
            'service_description' => 'Admin only',
            'service_image' => 'haircut.jpg',
            'service_price' => 100,
            'service_duration' => 45,
            'service_status' => 'hidden',
        ]);

        // 2. Query as a regular user (defaults to user role)
        $user = \App\Models\User::create([
            'user_name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
        \Laravel\Sanctum\Sanctum::actingAs($user);

        $response = $this->getJson('/api/services');
        $response->assertStatus(200);
        // Should only return 1 service (the published one)
        $response->assertJsonCount(1, 'data.data');
        $response->assertJsonPath('data.data.0.id', $publishedService->id);

        // 3. Regular user tries to book the hidden service -> should fail
        $apptPayload = [
            'customer_phone' => '01011112222',
            'customer_name' => 'Regular Customer',
            'appointment_date' => '2026-06-25',
            'appointment_time' => '14:30:00',
            'service_ids' => [$hiddenService->id],
            'source' => 'online',
        ];
        $response = $this->postJson('/api/appointments', $apptPayload);
        $response->assertStatus(400);
        $response->assertJsonPath('message', 'بعض الخدمات المحددة غير متاحة للحجز حالياً.');

        // 4. Query as an admin
        $admin = \App\Models\User::create([
            'user_name' => 'Admin User 2',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        \Laravel\Sanctum\Sanctum::actingAs($admin);

        $response = $this->getJson('/api/services');
        $response->assertStatus(200);
        // Admin gets both services (since we also created some in previous tests, but database is fresh/refreshed)
        $response->assertJsonCount(2, 'data.data');
    }
}
