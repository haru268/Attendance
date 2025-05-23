<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakRecord;
use Carbon\Carbon;

class AttendanceDetailAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者が勤怠詳細を見て承認ボタンを確認できる()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user  = User::factory()->create(['is_admin' => false]);

        $att = Attendance::create([
            'user_id'   => $user->id,
            'date'      => Carbon::today()->toDateString(),
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);
        BreakRecord::create([
            'attendance_id' => $att->id,
            'break_start'   => '12:00',
            'break_end'     => '13:00',
        ]);

        $this->actingAs($admin)
             ->get(route('admin.attendance.detail', ['key' => $att->id]))
             ->assertStatus(200)
             // 承認ボタンがある
             ->assertSee('承認');
    }
}
