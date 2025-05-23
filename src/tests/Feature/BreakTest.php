<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 休憩開始と終了が記録される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // まず出勤
        $this->post(route('attendance.clock'), ['type' => 'clock_in']);

        // 休憩開始
        $this->post(route('attendance.clock'), ['type' => 'break_in'])
             ->assertStatus(302);

        $this->assertDatabaseCount('break_records', 1);

        // 休憩終了
        $this->post(route('attendance.clock'), ['type' => 'break_out'])
             ->assertStatus(302);

        $this->assertDatabaseHas('break_records', [
            'attendance_id' => $attendance->id,
            // break_end が null でないことだけ確認
        ]);
    }
}
