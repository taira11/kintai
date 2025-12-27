<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyPageTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test */
    public function selling_products_are_displayed_by_default()
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'nickname' => 'テストユーザー',
            'postal_code' => '123-4567',
            'address' => '東京都',
        ]);

        Product::factory()->create([
            'name' => '出品商品',
            'seller_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/mypage');

        $response->assertStatus(200);
        $response->assertSee('出品商品');
        $response->assertDontSee('購入商品');
    }

    /** @test */
    public function bought_products_are_displayed_when_bought_tab_is_selected()
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        $buyer->profile()->create([
            'nickname' => '購入者',
            'postal_code' => '123-4567',
            'address' => '東京都',
        ]);

        $product = Product::factory()->create([
            'name' => '購入商品',
            'seller_id' => $seller->id,
        ]);

        Transaction::create([
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'buyer_id' => $buyer->id,
            'price' => 1000,
            'payment_method' => 'stripe',
            'shipping_address' => 'テスト住所',
            'status' => 1,
            'purchased_at' => now(),
        ]);

        $response = $this->actingAs($buyer)->get('/mypage?tab=bought');

        $response->assertStatus(200);
        $response->assertSee('購入商品');
        $response->assertDontSee('出品商品');
    }
}
