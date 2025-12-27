<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemIndexTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test */
    public function all_products_are_displayed()
    {
        $products = Product::factory()->count(3)->create();

        $response = $this->get('/');

        $response->assertStatus(200);

        foreach ($products as $product) {
            $response->assertSee($product->name);
        }
    }

    /** @test */
    public function sold_product_is_displayed_as_sold()
    {
        $seller = User::factory()->create();

        $product = Product::factory()->create([
            'seller_id' => $seller->id,
        ]);

        $buyer = User::factory()->create();

        Transaction::factory()->create([
            'product_id' => $product->id,
            'seller_id'  => $seller->id,
            'buyer_id'   => $buyer->id,
        ]);

        $response = $this->get('/');

        $response->assertSee('SOLD');
    }

    /** @test */
    public function own_products_are_not_displayed()
    {
        $user = User::factory()->create();

        $ownProduct = Product::factory()->create([
            'seller_id' => $user->id,
            'name' => '自分の商品',
        ]);

        $otherProduct = Product::factory()->create([
            'name' => '他人の商品',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertDontSee('自分の商品');

        $response->assertSee('他人の商品');
    }
}
