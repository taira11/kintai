<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Favorite;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test */
    public function liked_products_are_displayed_in_mylist()
    {
        $user = User::factory()->create();

        $likedProduct = Product::factory()->create(['name' => 'LIKED_PRODUCT_ONLY',]);
        $notLikedProduct = Product::factory()->create(['name' => 'NOT_LIKED_PRODUCT_SHOULD_NOT_APPEAR',]);

        DB::table('favorites')->insert([
            'user_id' => $user->id,
            'product_id' => $likedProduct->id,
        ]);

        $response = $this->actingAs($user)->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertSee($likedProduct->name);
        $response->assertDontSee($notLikedProduct->name);
    }

    /** @test */
    public function sold_product_is_displayed_as_sold_in_mylist()
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        $product = Product::factory()->create([
            'seller_id' => $seller->id,
        ]);

        DB::table('favorites')->insert([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
        ]);

        Transaction::create([
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'buyer_id' => $buyer->id,
            'price' => 1000,
            'payment_method' => 'card',
            'shipping_address' => 'test',
            'status' => 1,
            'purchased_at' => now(),
        ]);

        $response = $this->actingAs($buyer)->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertSee('SOLD');
    }

    /** @test */
    public function guest_user_sees_nothing_in_mylist()
    {
        $product = Product::factory()->create(['name' => 'TEST_PRODUCT_NOT_VISIBLE']);

        $response = $this->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertDontSee($product->name);
    }
}
