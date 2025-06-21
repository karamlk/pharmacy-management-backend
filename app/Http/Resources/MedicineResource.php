<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'barcode'=>$this->barcode,
            'manufacturer' => $this->manufacturer,
            'active_ingredient' => $this->active_ingredient,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'production_date' => $this->production_date,
            'expiry_date' => $this->expiry_date,
            'img_url' => asset($this->img_url),
            'category_name' => $this->category?->name
        ];
    }
}
