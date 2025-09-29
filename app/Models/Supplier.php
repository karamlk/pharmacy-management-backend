<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;


class Supplier extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['name', 'email', 'phone','address', 'balance'];

    public function orders()
    {
        return $this->hasMany(SupplierOrder::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }
}
