<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    const STATUS_PENDING   = 0;
    const STATUS_COMPLETED = 1;

    protected $fillable = [
        'product_id',
        'seller_id',
        'buyer_id',
        'price',
        'payment_method',
        'shipping_address',
        'status',
        'purchased_at',
        'completed_at',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}
