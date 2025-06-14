<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'img_url'];

    public static function booted()
    {
        static::deleting(function ($category) {
            if ($category->name === 'Uncategorized') {
                throw new \Exception('Cannot delete the Uncategorized category.');
            }

            $fallback = self::where('name', 'Uncategorized')->first();
            if ($fallback) {
                Medicine::where('category_id', $category->id)
                    ->update(['category_id' => $fallback->id]);
            }
        });
    }

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }
}
