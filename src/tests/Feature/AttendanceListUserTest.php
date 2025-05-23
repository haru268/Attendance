<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakRecord;
use Carbon\Carbon;

class AttendanceListUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠一覧が表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 今月の1日と2日にダミーデータ作成
        foreach ([1, 2] as $day) {
            $date = Carbon::create(now()->year, now()->month, $day);
            $att = Attendance::create([
                'user_id'   => $user->id,
                'date'      => $date->toDateString(),
                'clock_in'  => '09:00',
                'clock_out' => '18:00',
            ]);
            BreakRecord::create([
                'attendance_id' => $att->id,
                'break_start'   => '12:00',
                'break_end'     => '13:00',
            ]);
        }

        // 月次一覧にアクセス
        $this->get(route('attendance.list', [
                'year'  => now()->year,
                'month' => now()->month,
            ]))
            ->assertStatus(200)
            // 1日, 2日の行があるか
            ->assertSee(now()->format('Y/m'))
            ->assertSee('09:00')
            ->assertSee('18:00');
    }
}
