<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierOrder extends Model
{
    protected $fillable = ['supplier_id', 'user_id', 'order_date', 'total_price'];
    
    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(SupplierOrderItem::class);
    }
}
