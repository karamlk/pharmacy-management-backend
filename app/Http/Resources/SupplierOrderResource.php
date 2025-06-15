<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
           'supplier_name' => $this->supplier?->name,
            'pharmacist_name' => $this->user?->name,
            'order_date'=>$this->order_date,
            'total_price'=>$this->total_price
        ];
    }
}
