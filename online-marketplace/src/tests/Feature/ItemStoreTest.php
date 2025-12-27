<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemStoreTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test */
    public function user_can_store_product_with_required_information()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        $category = Category::factory()->create(['name' => '家電']);

        $this->post('/sell', [
            'name'         => 'テスト商品',
            'brand'        => 'テストブランド',
            'description'  => 'テスト説明',
            'price'        => 10000,
            'status'       => 1,
            'category_ids' => (string) $category->id,
            'image'        => UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg'),
        ]);

        $this->assertDatabaseHas('products', [
            'name'      => 'テスト商品',
            'brand'     => 'テストブランド',
            'price'     => 10000,
            'seller_id'=> $user->id,
        ]);

        $product = Product::first();

        $this->assertDatabaseHas('product_categories', [
            'product_id'  => $product->id,
            'category_id' => $category->id,
        ]);
    }
}
