<?php

namespace Tests\Feature;


use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;

class PurchaseTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test */
    public function user_can_purchase_a_product()
    {
        $seller = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $buyer = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $buyer->profile()->create([
            'nickname'    => '購入者',
            'postal_code' => '123-4567',
            'address'     => '東京都',
            'building'    => 'テストビル',
        ]);

        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'status'    => Product::STATUS_ON_SALE,
        ]);

        $this->actingAs($buyer)
            ->get(route('purchase.success', $product->id));

        $this->assertDatabaseHas('transactions', [
            'product_id' => $product->id,
            'buyer_id'   => $buyer->id,
            'seller_id'  => $seller->id,
        ]);

        $this->assertDatabaseHas('products', [
            'id'     => $product->id,
            'status' => Product::STATUS_SOLD,
        ]);
    }

    /** @test */
    public function purchased_product_is_marked_as_sold_in_item_list()
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => Product::STATUS_SOLD,
        ]);

        Transaction::create([
            'product_id'       => $product->id,
            'seller_id'        => $seller->id,
            'buyer_id'         => $buyer->id,
            'price'            => 1000,
            'payment_method'   => 'card',
            'shipping_address' => 'test address',
            'status'           => 1,
            'purchased_at'     => now(),
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('SOLD');
    }

    /** @test */
    public function purchased_product_is_displayed_in_user_profile()
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'name' => '購入した商品',
            'status' => Product::STATUS_SOLD,
        ]);

        Transaction::create([
            'product_id'       => $product->id,
            'seller_id'        => $seller->id,
            'buyer_id'         => $buyer->id,
            'price'            => 1000,
            'payment_method'   => 'card',
            'shipping_address' => 'test address',
            'status'           => 1,
            'purchased_at'     => now(),
        ]);

        $response = $this->actingAs($buyer)
            ->get('/mypage?tab=buy');

        $response->assertStatus(200);
        $response->assertSee('購入した商品');
    }
}
