<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthUserRegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 一般ユーザーを正しく登録できる()
    {
        // 会員登録画面にアクセス
        $this->get(route('register.form'))
             ->assertStatus(200)
             ->assertSee('会員登録');

        // フォーム送信
        $response = $this->post(route('register'), [
            'name'                  => 'テスト太郎',
            'email'                 => 'taro@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        // DB に保存されていること
        $this->assertDatabaseHas('users', [
            'email'    => 'taro@example.com',
            'is_admin' => false,
        ]);

        // ログイン後、打刻画面へリダイレクト
        $response->assertRedirect(route('attendance'));
    }

    /** @test */
    public function パスワードが8文字未満だとエラーになる()
    {
        $this->from(route('register.form'))
             ->post(route('register'), [
                 'name'                  => '太郎',
                 'email'                 => 'taro2@example.com',
                 'password'              => 'short',
                 'password_confirmation' => 'short',
             ])
             ->assertRedirect(route('register.form'))
             ->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }
}
