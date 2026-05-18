<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'cashier_id',
        'subtotal',
        'tax',
        'total',
        'payment_method',
        'order_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'cashier_id'   => 'integer',
        'subtotal'     => 'integer',
        'tax'          => 'integer',
        'total'        => 'integer',
        'order_date'   => 'datetime',
    ];

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total ?? 0, 0, ',', '.');
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'APC-';
        $latest = self::withTrashed()->latest('id')->first();

        $next = 1;
        if ($latest && $latest->order_number) {
            $number = str_replace($prefix, '', $latest->order_number);
            $next = ((int) $number) + 1;
        }

        return $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}