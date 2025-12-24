<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'user_id',
        'recipient_name',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'subtotal',
        'shipping_cost',
        'total_amount',
        'payment_method',
        'shipping_method',
        'resi_code',
        'status',
        'design_status',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    // Boot method to generate order code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_code) {
                $order->order_code = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            }
        });
    }
}
