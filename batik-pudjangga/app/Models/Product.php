<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category',
        'subcategory',
        'image',
        'is_new',
        'is_sale',
        'sale_price',
        'sale_start',
        'sale_end',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'is_new' => 'boolean',
            'is_sale' => 'boolean',
            'sale_start' => 'date',
            'sale_end' => 'date',
        ];
    }

    // Relationships
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Accessors
    public function getCurrentPriceAttribute()
    {
        if ($this->is_sale && $this->sale_price && $this->isSaleActive()) {
            return $this->sale_price;
        }
        return $this->price;
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->is_sale && $this->sale_price) {
            return round((($this->price - $this->sale_price) / $this->price) * 100);
        }
        return 0;
    }

    // Scopes
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeNew($query)
    {
        return $query->where('is_new', true);
    }

    public function scopeOnSale($query)
    {
        return $query->where('is_sale', true)
            ->whereNotNull('sale_price')
            ->where(function ($q) {
                $q->whereNull('sale_start')
                  ->orWhere('sale_start', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('sale_end')
                  ->orWhere('sale_end', '>=', now());
            });
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    // Methods
    public function isSaleActive()
    {
        if (!$this->is_sale) {
            return false;
        }

        $now = now();
        
        if ($this->sale_start && $now->lt($this->sale_start)) {
            return false;
        }

        if ($this->sale_end && $now->gt($this->sale_end)) {
            return false;
        }

        return true;
    }
}

