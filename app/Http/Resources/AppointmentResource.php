<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'appointment_date' => $this->appointment_date,
            'appointment_time' => $this->appointment_time ? date('H:i', strtotime($this->appointment_time)) : null,
            'source' => $this->source,
            'appointment_status' => $this->appointment_status,
            'appointment_notes' => $this->appointment_notes,
            'total_price' => isset($this->total_price) ? (float) $this->total_price : ($this->services ? (float) $this->services->sum('service_price') : 0.0),
            'invoice_id' => $this->invoice?->id,
            'rating_status' => $this->invoice?->rating_status ?? 'open',
            'customer' => $this->customer ? [
                'id' => $this->customer->id,
                'customer_name' => $this->customer->customer_name,
                'customer_phone' => $this->customer->customer_phone,
            ] : null,
            'services' => $this->services ? $this->services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'service_name' => $service->service_name,
                    'service_price' => (float) $service->service_price,
                    'service_duration' => (int) $service->service_duration,
                ];
            }) : [],
        ];
    }
}
