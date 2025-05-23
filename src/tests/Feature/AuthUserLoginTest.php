<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AuthUserLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤務外ステータスが表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // レコードがないので「勤務外」
        $this->get(route('attendance'))
             ->assertSee('勤務外');
    }

    /** @test */
    public function 出勤中ステータスが表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤のみ記録
        Attendance::create([
            'user_id'  => $user->id,
            'date'     => Carbon::today(),
            'clock_in' => now(),
        ]);

        $this->get(route('attendance'))
             ->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中ステータスが表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤＋休憩開始のみ記録
        $att = Attendance::create([
            'user_id'  => $user->id,
            'date'     => Carbon::today(),
            'clock_in' => now(),
        ]);
        $att->breakRecords()->create(['break_start' => now()]);

        $this->get(route('attendance'))
             ->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済ステータスが表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤＋退勤記録
        Attendance::create([
            'user_id'   => $user->id,
            'date'      => Carbon::today(),
            'clock_in'  => now(),
            'clock_out' => now(),
        ]);

        $this->get(route('attendance'))
             ->assertSee('退勤済');
    }
}
