<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BarberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barber_name' => $this->barber_name,
            'barber_phone' => $this->barber_phone,
            'barber_nid' => $this->barber_nid,
            'salary' => (float) $this->salary,
            'barber_status' => $this->barber_status,
        ];
    }
}
