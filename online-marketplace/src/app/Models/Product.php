<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    const STATUS_ON_SALE = 0;
    const STATUS_SOLD    = 1;
    const STATUS_BAD = 1;
    const STATUS_FAIR = 2;
    const STATUS_GOOD = 3;
    const STATUS_EXCELLENT = 4;

    protected $casts = [
        'status' => 'integer',
    ];

    protected $fillable = [
        'seller_id',
        'name',
        'brand',
        'description',
        'price',
        'status',
        'image'
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories', 'product_id', 'category_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'product_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'product_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'product_id');
    }

    public function getIsFavoritedAttribute()
    {
        if (!auth()->check()) return false;

        return $this->favorites()
                    ->where('user_id', auth()->id())
                    ->exists();
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function getIsSoldAttribute()
    {
        return $this->transaction()->exists();
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            self::STATUS_EXCELLENT => '良好',
            self::STATUS_GOOD => '目立った傷や汚れなし',
            self::STATUS_FAIR => 'やや傷や汚れあり',
            self::STATUS_BAD => '状態が悪い',
            default => '',
        };
    }
}
