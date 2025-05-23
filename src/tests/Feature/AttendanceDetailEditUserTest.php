<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\RevisionRequest;
use Carbon\Carbon;

class AttendanceDetailEditUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 修正申請が作成される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 今日の勤怠
        $att = Attendance::create([
            'user_id'   => $user->id,
            'date'      => Carbon::today()->toDateString(),
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);

        // フォーム送信(PATCH)
        $response = $this->patch(route('attendance.update', $att->id), [
            'clock_in'            => '09:30',
            'clock_out'           => '17:30',
            'remarks'             => '修正理由',
            'breaks'              => [['start'=>'12:00','end'=>'13:00']],
        ]);

        $response->assertStatus(302);

        // revision_requests テーブルに Pending レコードが存在
        $this->assertDatabaseHas('revision_requests', [
            'user_id'             => $user->id,
            'attendance_id'       => $att->id,
            'proposed_clock_in'   => '09:30',
            'proposed_clock_out'  => '17:30',
            'proposed_remarks'    => '修正理由',
            'status'              => 'pending',
        ]);
    }
}
