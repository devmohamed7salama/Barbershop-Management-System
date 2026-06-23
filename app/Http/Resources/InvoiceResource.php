<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'total_price'      => (float) $this->total_price, // Cast to float
            'total_services'   => (int) $this->invoiceitems->count(), // مجموع الخدمات المنفذة
            'created_at'       => $this->created_at->format('Y-m-d h:i A'), // شياكة في العرض
            
            // العميل (خدنا الاسم والتليفون بس)
            'customer'         => [
                'id'    => $this->customer?->id,
                'name'  => $this->customer?->customer_name,
                'phone' => $this->customer?->customer_phone,
            ],

            // الحلاق (خفينا الرقم القومي والمرتب)
            'barber_name'      => $this->barber?->barber_name,

            // تفاصيل الحجز
            'appointment_date' => $this->appointment?->appointment_date,
            'appointment_time' => $this->appointment && $this->appointment->appointment_time ? date('H:i', strtotime($this->appointment->appointment_time)) : null,

            // لفينا على عناصر الفاتورة وخدنا اللي يهمنا
            'items'            => $this->invoiceitems->map(function ($item) {
                return [
                    'service_id' => $item->service_id,
                    'service_name' => $item->service?->service_name,
                    'price'      => (float) $item->price,
                ];
            }),
        ];
    }
}
