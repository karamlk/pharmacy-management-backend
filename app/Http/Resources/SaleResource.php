<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pharmacist_name' => $this->user?->name,
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date,
            'total_price' => $this->total_price
        ];
    }
}
