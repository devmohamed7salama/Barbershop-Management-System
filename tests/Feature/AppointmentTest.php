<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Barber;
use App\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
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

    public function test_can_get_appointments_with_services()
    {
        // 1. Create a customer
        $customer = Customer::create([
            'customer_name' => 'John Doe',
            'customer_phone' => '01234567890',
        ]);

        // 2. Create a service
        $service = Service::create([
            'service_name' => 'Haircut',
            'service_description' => 'A basic haircut',
            'service_image' => 'haircut.jpg',
            'service_price' => 15.00,
            'service_duration' => 30,
        ]);

        // 3. Create an appointment
        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'appointment_date' => '2026-06-25',
            'appointment_time' => '14:00:00',
            'source' => 'online',
            'appointment_status' => 'pending',
            'appointment_notes' => 'Some notes',
        ]);

        // 4. Attach service to appointment
        $appointment->services()->attach($service->id);

        // 5. Fetch api route
        $response = $this->getJson('/api/appoiments');

        // 6. Assert success
        $response->assertStatus(200);
        $response->assertJsonPath('data.data.0.customer.customer_name', 'John Doe');
        $response->assertJsonPath('data.data.0.services.0.service_name', 'Haircut');
    }

    public function test_can_create_appointment_and_calculate_total_price()
    {
        $service1 = Service::create([
            'service_name' => 'Haircut',
            'service_description' => 'A basic haircut',
            'service_image' => 'haircut.jpg',
            'service_price' => 15.00,
            'service_duration' => 30,
        ]);

        $service2 = Service::create([
            'service_name' => 'Beard Trim',
            'service_description' => 'A trim for beard',
            'service_image' => 'beard.jpg',
            'service_price' => 10.00,
            'service_duration' => 20,
        ]);

        $payload = [
            'customer_name' => 'Jane Smith',
            'customer_phone' => '09876543210',
            'appointment_date' => '2026-06-26',
            'appointment_time' => '15:30:00',
            'service_ids' => [$service1->id, $service2->id],
            'source' => 'offline',
            'appointment_notes' => 'Looking forward to it',
        ];

        $response = $this->postJson('/api/appoiments/create', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('data.total_price', 25);
        $response->assertJsonPath('data.customer.customer_name', 'Jane Smith');
        $response->assertJsonCount(2, 'data.services');
    }

    public function test_can_show_appointment()
    {
        $customer = Customer::create([
            'customer_name' => 'Bob Marley',
            'customer_phone' => '01111111111',
        ]);

        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'appointment_date' => '2026-06-25',
            'appointment_time' => '10:00:00',
            'source' => 'online',
        ]);

        $response = $this->getJson("/api/appoiments/{$appointment->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.customer.customer_name', 'Bob Marley');
    }

    public function test_can_update_appointment()
    {
        $customer = Customer::create([
            'customer_name' => 'Alice Cooper',
            'customer_phone' => '02222222222',
        ]);

        $service = Service::create([
            'service_name' => 'Hairwash',
            'service_description' => 'Clean and wash hair',
            'service_image' => 'wash.jpg',
            'service_price' => 5.00,
            'service_duration' => 10,
        ]);

        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'appointment_date' => '2026-06-25',
            'appointment_time' => '10:00:00',
            'source' => 'online',
        ]);

        $payload = [
            'appointment_date' => '2026-06-27',
            'appointment_time' => '12:00:00',
            'service_ids' => [$service->id],
            'appointment_status' => 'completed',
        ];

        $response = $this->postJson("/api/appoiments/update/{$appointment->id}", $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('data.total_price', 5);
        $response->assertJsonPath('data.appointment_status', 'completed');
        $response->assertJsonPath('data.appointment_date', '2026-06-27');
    }

    public function test_can_delete_appointment()
    {
        $customer = Customer::create([
            'customer_name' => 'Alice Cooper',
            'customer_phone' => '02222222222',
        ]);

        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'appointment_date' => '2026-06-25',
            'appointment_time' => '10:00:00',
            'source' => 'online',
        ]);

        $response = $this->postJson("/api/appoiments/delete/{$appointment->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);
    }

    public function test_can_update_appointment_status()
    {
        $customer = Customer::create([
            'customer_name' => 'John Legend',
            'customer_phone' => '03333333333',
        ]);

        $barber = Barber::create([
            'barber_name' => 'Barber Joe',
            'barber_phone' => '01011111111',
            'barber_nid' => '12345678901234',
            'salary' => 500.00,
            'barber_status' => 'available',
        ]);

        $shift = Shift::create([
            'start_time' => '08:00:00',
            'shift_status' => 'open',
        ]);

        $service = Service::create([
            'service_name' => 'Haircut',
            'service_description' => 'Haircut service',
            'service_image' => 'haircut.jpg',
            'service_price' => 50.00,
            'service_duration' => 30,
        ]);

        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'appointment_date' => '2026-06-25',
            'appointment_time' => '10:00:00',
            'source' => 'online',
            'appointment_status' => 'pending',
        ]);

        $appointment->services()->attach($service->id);

        $response = $this->postJson("/api/appoiments/status/{$appointment->id}", [
            'appointment_status' => 'completed',
            'barber_id' => $barber->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.appointment_status', 'completed');
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'appointment_status' => 'completed',
        ]);

        // Assert invoice and invoice items were created automatically
        $this->assertDatabaseHas('invoices', [
            'customer_id' => $customer->id,
            'barber_id' => $barber->id,
            'shift_id' => $shift->id,
            'appointment_id' => $appointment->id,
            'total_price' => 50.00,
        ]);
    }
}
