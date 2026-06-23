<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'visit_count' => isset($this->visit_count) ? (int) $this->visit_count : null,
            'total_spent' => isset($this->total_spent) ? (float) $this->total_spent : null,
        ];
    }
}
