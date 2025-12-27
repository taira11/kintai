<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test */
    public function product_detail_page_displays_all_required_information()
    {
        $user = User::factory()->create();

        $user->profile()->create([
            'nickname'    => 'テストユーザー',
            'postal_code' => '123-4567',
            'address'     => '東京都テスト区',
            'building'    => 'テストビル',
        ]);

        $product = Product::factory()->create([
            'seller_id' => $user->id,
            'name'      => 'テスト商品',
            'brand'     => 'テストブランド',
            'price'     => 1000,
            'description' => 'テスト説明',
        ]);

        $response = $this->get("/item/{$product->id}");

        $response->assertStatus(200);
        $response->assertSee('テスト商品');
        $response->assertSee('テストブランド');
        $response->assertSee('1,000');
        $response->assertSee('テスト説明');
    }

    /** @test */
    public function multiple_categories_are_displayed_on_product_detail_page()
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'seller_id' => $user->id,
        ]);

        $categories = Category::factory()->count(2)->create();

        $product->categories()->attach($categories->pluck('id'));

        $response = $this->get("/item/{$product->id}");
        $response->assertStatus(200);

        foreach ($categories as $category) {
            $response->assertSee($category->name);
        }
    }
}
