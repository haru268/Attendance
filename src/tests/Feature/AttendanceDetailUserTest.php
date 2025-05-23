<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakRecord;
use Carbon\Carbon;

class AttendanceDetailUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面に打刻内容が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 今日の勤怠に出勤・退勤・休憩を登録
        $att = Attendance::create([
            'user_id'   => $user->id,
            'date'      => Carbon::today()->toDateString(),
            'clock_in'  => '09:30',
            'clock_out' => '17:45',
            'remarks'   => 'テスト備考',
        ]);
        BreakRecord::create([
            'attendance_id' => $att->id,
            'break_start'   => '12:00',
            'break_end'     => '12:45',
        ]);

        // 詳細画面へ
        $this->get(route('attendance.detail', ['key' => $att->date]))
             ->assertStatus(200)
             ->assertSee('09:30')
             ->assertSee('17:45')
             ->assertSee('12:00')
             ->assertSee('12:45')
             ->assertSee('テスト備考');
    }
}
