<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use SoftDeletes;

    protected $fillable = ['category_id', 'name', 'barcode', 'manufacturer', 'active_ingredient', 'price', 'quantity', 'production_date', 'expiry_date', 'img_url'];

    public function category(): BelongsTo
    {
        return  $this->belongsTo(Category::class);
    }

    public function scopeAvailable(Builder $query)
    {
        return $query->where('quantity', '>', 0)
            ->whereDate('expiry_date', '>=', now()->toDateString());
    }
}
