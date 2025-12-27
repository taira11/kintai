<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test */
    public function logged_in_user_can_post_comment()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->post(
            "/item/{$product->id}/comment",
            ['content' => 'テストコメント']
        );

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'comment' => 'テストコメント',
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function guest_user_cannot_post_comment()
    {
        $product = Product::factory()->create();

        $response = $this->post(
            "/item/{$product->id}/comment",
            ['content' => 'テストコメント']
        );

        $this->assertDatabaseCount('comments', 0);

        $response->assertRedirect('/login');
    }

    /** @test */
    public function comment_is_required()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->post(
            "/item/{$product->id}/comment",
            ['content' => '']
        );

        $response->assertSessionHasErrors([
            'content' => 'コメントを入力してください',
        ]);
    }

    /** @test */
    public function comment_cannot_exceed_255_characters()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $longComment = str_repeat('あ', 256);

        $response = $this->actingAs($user)->post(
            "/item/{$product->id}/comment",
            ['content' => $longComment]
        );

        $response->assertSessionHasErrors([
            'content' => 'コメントは255文字以内で入力してください',
        ]);
    }
}
