<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'color',
        'size',
        'stock',
        'price_adjustment',
    ];

    protected function casts(): array
    {
        return [
            'price_adjustment' => 'decimal:2',
        ];
    }

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessor
    public function getFinalPriceAttribute()
    {
        return $this->product->current_price + $this->price_adjustment;
    }
}

