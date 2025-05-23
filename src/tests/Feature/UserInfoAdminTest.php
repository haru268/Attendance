<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserInfoAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者がスタッフ一覧でユーザー情報を確認できる()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $u1 = User::factory()->create(['name' => '田中太郎', 'email' => 'taro@example.com']);
        $u2 = User::factory()->create(['name' => '鈴木花子', 'email' => 'hanako@example.com']);

        $this->actingAs($admin)
             ->get(route('admin.staff.list'))
             ->assertStatus(200)
             ->assertSee('田中太郎')
             ->assertSee('taro@example.com')
             ->assertSee('鈴木花子')
             ->assertSee('hanako@example.com');
    }
}
