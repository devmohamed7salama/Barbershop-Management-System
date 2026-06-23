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

class DashboardTest extends TestCase
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

    public function test_dashboard_stats_endpoint_returns_correct_structure()
    {
        // 1. Create a customer
        $customer = Customer::create([
            'customer_name' => 'Test Customer',
            'customer_phone' => '01011111111',
        ]);

        // 2. Create a barber
        $barber = Barber::create([
            'barber_name' => 'Test Barber',
            'barber_phone' => '01211111111',
            'barber_nid' => '11111111111111',
            'salary' => 500.00,
            'barber_status' => 'available',
        ]);

        // 3. Create a shift
        $shift = Shift::create([
            'start_time' => '08:00:00',
            'shift_status' => 'open',
        ]);

        // 4. Create a service
        $service = Service::create([
            'service_name' => 'Haircut',
            'service_description' => 'Haircut description',
            'service_image' => 'haircut.jpg',
            'service_price' => 50.00,
            'service_duration' => 30,
        ]);

        // 5. Create an appointment and complete it
        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'appointment_date' => '2026-06-25',
            'appointment_time' => '10:00:00',
            'source' => 'online',
            'appointment_status' => 'completed',
        ]);

        $appointment->services()->attach($service->id);

        // 6. Create invoice
        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'barber_id' => $barber->id,
            'shift_id' => $shift->id,
            'appointment_id' => $appointment->id,
            'total_price' => 50.00,
        ]);

        // 7. Call the dashboard stats endpoint
        $response = $this->getJson('/api/dashboard/stats');

        // 8. Assertions
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'status',
            'data' => [
                'stats',
                'most_visiting_customers',
                'most_paying_customers',
                'recent_haircuts',
                'recent_shifts',
                'popular_services',
            ]
        ]);
        
        $response->assertJsonCount(4, 'data.stats');
        $response->assertJsonCount(1, 'data.most_visiting_customers');
        $response->assertJsonCount(1, 'data.most_paying_customers');
        $response->assertJsonCount(1, 'data.recent_haircuts');
        $response->assertJsonCount(1, 'data.recent_shifts');
        $response->assertJsonCount(1, 'data.popular_services');
    }
}
