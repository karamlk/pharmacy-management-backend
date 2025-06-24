<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $table = 'sales';
    protected $fillable = ['user_id','invoice_number','invoice_date','total_price'];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function sale_items(){
        return $this->hasMany(SaleItem::class);
    }

}
