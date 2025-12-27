<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'product_id'       => Product::factory(),
            'seller_id'        => User::factory(),
            'buyer_id'         => User::factory(),
            'price'            => 1000,
            'payment_method'   => 'card',
            'shipping_address' => '東京都渋谷区',
            'status'           => 1,
            'purchased_at'     => now(),
        ];
    }
}
