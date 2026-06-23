<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_name' => $this->service_name,
            'service_description' => $this->service_description,
            'service_image' => $this->service_image,
            'service_price' => (float) $this->service_price,
            'service_duration' => (int) $this->service_duration,
            'service_status' => $this->service_status ?? 'published',
            'demand_count' => isset($this->demand_count) ? (int) $this->demand_count : null,
        ];
    }
}
