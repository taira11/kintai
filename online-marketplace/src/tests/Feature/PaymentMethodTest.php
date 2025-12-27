<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentMethodTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test */
    public function selected_payment_method_is_reflected_on_purchase_screen()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $user->profile()->create([
            'nickname'    => 'テストユーザー',
            'postal_code' => '123-4567',
            'address'     => '東京都',
        ]);

        $product = Product::factory()->create();

        $this->actingAs($user)
            ->withSession([
                "payment_method_{$product->id}" => 'konbini',
            ])
            ->get(route('purchase.index', $product->id))
            ->assertStatus(200)
            ->assertSee('konbini');
    }
}
