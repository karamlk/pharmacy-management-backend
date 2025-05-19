<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Medicine extends Model
{
    protected $fillable = ['category_id', 'name', 'manufacturer', 'price', 'stock', 'expiry_date'];

    public function category(): BelongsTo
    {
        return  $this->belongsTo(Category::class);
    }
}
