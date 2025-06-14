<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Medicine extends Model
{
    protected $fillable = ['category_id', 'name', 'manufacturer','active_ingredient','price', 'quantity','production_date', 'expiry_date','img_url'];

    public function category(): BelongsTo
    {
        return  $this->belongsTo(Category::class);
    }
}
