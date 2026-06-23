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

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_invoice_and_calculate_prices_automatically()
    {
        // 1. Create a customer
        $customer = Customer::create([
            'customer_name' => 'John Doe',
            'customer_phone' => '01234567890',
        ]);

        // 2. Create a barber
        $barber = Barber::create([
            'barber_name' => 'Barber Joe',
            'barber_phone' => '01011111111',
            'barber_nid' => '12345678901234',
            'salary' => 500.00,
            'barber_status' => 'available',
        ]);

        // 3. Create a shift
        $shift = Shift::create([
            'start_time' => '08:00:00',
            'shift_status' => 'open',
        ]);

        // 4. Create an appointment
        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'appointment_date' => '2026-06-25',
            'appointment_time' => '14:30:00',
            'source' => 'online',
        ]);

        // 5. Create services with prices
        $service1 = Service::create([
            'service_name' => 'Haircut',
            'service_description' => 'Haircut service',
            'service_image' => 'haircut.jpg',
            'service_price' => 50.00,
            'service_duration' => 30,
        ]);

        $service2 = Service::create([
            'service_name' => 'Beard Trim',
            'service_description' => 'Beard trim service',
            'service_image' => 'beard.jpg',
            'service_price' => 30.00,
            'service_duration' => 20,
        ]);

        // 6. Send request to create invoice
        $payload = [
            'customer_id' => $customer->id,
            'barber_id' => $barber->id,
            'appointment_id' => $appointment->id,
            'items' => [
                [
                    'service_id' => $service1->id,
                ],
                [
                    'service_id' => $service2->id,
                ]
            ]
        ];

        $response = $this->postJson('/api/invoices/create', $payload);

        // 7. Assert invoice was created with correct total_price (50 + 30 = 80)
        $response->assertStatus(201);
        $response->assertJsonPath('invoice.total_price', 80);
        $response->assertJsonPath('invoice.items.0.price', 50);
        $response->assertJsonPath('invoice.items.1.price', 30);
    }
}
