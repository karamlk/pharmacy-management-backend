<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $table = 'sale_items';
    protected $fillable = ['sale_id', 'medicine_id', 'quantity', 'unit_price'];
    public function medicine(){
        return $this->belongsTo('App\Models\Medicine');
    }
    public function sale(){
        return $this->belongsTo('App\Models\Sale');
    }
}
