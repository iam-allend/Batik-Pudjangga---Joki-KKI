<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recipient_name',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Boot method to handle default address
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($address) {
            if ($address->is_default) {
                // Set other addresses as non-default
                static::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}
