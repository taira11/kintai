<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PurchaseAddressTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test */
    public function updated_shipping_address_is_reflected_on_purchase_screen()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $user->profile()->create([
            'nickname'    => 'テストユーザー',
            'postal_code' => '111-1111',
            'address'     => '東京都旧住所',
        ]);

        $product = Product::factory()->create();

        $newAddress = '〒123-4567 東京都新宿区 テストマンション101';

        $this->actingAs($user)
            ->withSession([
                "shipping_{$product->id}" => $newAddress,
            ])
            ->get(route('purchase.index', $product->id))
            ->assertStatus(200)
            ->assertSee($newAddress);
    }

    /** @test */
    public function shipping_address_is_saved_when_product_is_purchased()
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
            'address'     => '東京都購入区',
            'building'    => 'テストビル',
        ]);

        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'price'     => 1000,
        ]);

        $shippingAddress = '〒999-9999 大阪府大阪市 テストタワー';

        $this->actingAs($buyer)->withSession([
            "shipping_{$product->id}" => $shippingAddress,
        ]);

        $this->get(route('purchase.success', $product->id));

        $this->assertDatabaseHas('transactions', [
            'product_id'       => $product->id,
            'buyer_id'         => $buyer->id,
            'seller_id'        => $seller->id,
            'shipping_address' => $shippingAddress,
        ]);
    }
}
