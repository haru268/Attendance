<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakRecord;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthAdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者が日次勤怠一覧を閲覧できる()
    {
        // 管理者
        $admin = User::factory()->create(['is_admin' => true]);
        // 一般ユーザーを２人作成し勤怠登録
        $u1 = User::factory()->create(['is_admin' => false]);
        $u2 = User::factory()->create(['is_admin' => false]);

        foreach ([$u1, $u2] as $u) {
            $att = Attendance::create([
                'user_id'   => $u->id,
                'date'      => Carbon::today()->toDateString(),
                'clock_in'  => '09:00',
                'clock_out' => '18:00',
            ]);
            BreakRecord::create([
                'attendance_id' => $att->id,
                'break_start'   => '12:00',
                'break_end'     => '13:00',
            ]);
        }

        $this->actingAs($admin)
             ->get(route('admin.attendance.list'))
             ->assertStatus(200)
             // ２ユーザー分の「09:00」が表示される
             ->assertSee('09:00', false)
             ->assertSee('18:00', false);
    }
}
