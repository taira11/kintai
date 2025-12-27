<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileEditTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test */
    public function profile_edit_page_displays_existing_user_information_as_default_values()
    {
        $user = User::factory()->create();

        $user->profile()->create([
            'nickname'     => 'テストユーザー',
            'postal_code'  => '123-4567',
            'address'      => '東京都テスト区',
            'building'     => 'テストビル',
            'profile_image'   => 'profiles/test.png',
        ]);

        $response = $this->actingAs($user)->get('/mypage/edit');

        $response->assertStatus(200);

        $response->assertSee('テストユーザー');
        $response->assertSee('123-4567');
        $response->assertSee('東京都テスト区');
        $response->assertSee('テストビル');

        $response->assertSee('storage/profiles/test.png');
    }
}
