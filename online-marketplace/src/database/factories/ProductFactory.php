<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Product::class;

    public function definition()
    {
        return [
            'seller_id'   => User::factory(),
            'name'        => $this->faker->word(),
            'brand'       => $this->faker->company(),
            'description' => $this->faker->sentence(),
            'price'       => 1000,
            'status'      => 0,
            'image'       => 'products/test.jpg',
        ];
    }

    public function sold()
    {
        return $this->state(fn () => [
            'status' => 1,
        ]);
    }
}
