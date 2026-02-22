<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    private function createAdmin(array $override = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ], $override));
    }

    /** @test */
    public function email_is_required()
    {
        $this->createAdmin();

        $res = $this->from('/admin/login')->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $res->assertRedirect('/admin/login');
        $res->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** @test */
    public function password_is_required()
    {
        $this->createAdmin();

        $res = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $res->assertRedirect('/admin/login');
        $res->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** @test */
    public function login_fails_with_invalid_credentials()
    {
        $this->createAdmin([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $res = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'wrong-admin@example.com',
            'password' => 'password123',
        ]);

        $res->assertRedirect('/admin/login');
        $res->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);

        $this->assertGuest('admin');
    }
}
