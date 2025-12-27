<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoriteTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test */
    public function user_can_favorite_a_product()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/item/{$product->id}/favorite");

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response->assertOk()
            ->assertJson([
                'favorited' => true,
                'count' => 1,
            ]);
    }

    /** @test */
    public function user_can_unfavorite_a_product()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->postJson("/item/{$product->id}/favorite");

        $response = $this->actingAs($user)
            ->postJson("/item/{$product->id}/favorite");

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response->assertOk()
            ->assertJson([
                'favorited' => false,
                'count' => 0,
            ]);
    }
}
