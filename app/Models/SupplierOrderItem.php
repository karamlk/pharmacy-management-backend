<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierOrderItem extends Model
{
    protected $fillable = ['supplier_order_id', 'medicine_id', 'quantity', 'unit_price'];

    public function order()
    {
        return $this->belongsTo(SupplierOrder::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class)->withTrashed();
    }
}
