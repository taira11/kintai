<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;



class ItemSearchTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test */
    public function products_can_be_searched_by_partial_name()
    {
        $product1 = Product::factory()->create([
            'name' => 'iPhone 15 Pro',
        ]);

        $product2 = Product::factory()->create([
            'name' => 'MacBook Air',
        ]);

        $response = $this->get('/?keyword=iPhone');

        $response->assertStatus(200);
        $response->assertSee($product1->name);
        $response->assertDontSee($product2->name);
    }

    /** @test */
    public function search_keyword_is_kept_when_switching_to_mylist()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $likedProduct = Product::factory()->create([
            'name' => 'Nintendo Switch',
        ]);

        $notLikedProduct = Product::factory()->create([
            'name' => 'PlayStation 5',
        ]);

        DB::table('favorites')->insert([
            'user_id' => $user->id,
            'product_id' => $likedProduct->id,
        ]);

        $response = $this->get('/?tab=mylist&keyword=Nintendo');

        $response->assertStatus(200);
        $response->assertSee($likedProduct->name);
        $response->assertDontSee($notLikedProduct->name);
    }
}
