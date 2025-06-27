<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierPaymentResource extends JsonResource
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
            'supplier_name'  => $this->supplier?->name,
            'processed_by'   => $this->user?->name,
            'amount_paid'    => $this->amount,
            'payment_date'   => $this->payment_date,
        ];
    }
}
