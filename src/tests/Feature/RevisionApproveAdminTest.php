<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\RevisionRequest;
use App\Models\BreakRecord;
use Carbon\Carbon;

class RevisionApproveAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者が修正申請を承認できる()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user  = User::factory()->create(['is_admin' => false]);

        // 元データ
        $att = Attendance::create([
            'user_id'   => $user->id,
            'date'      => Carbon::today()->toDateString(),
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'remarks'   => '元備考',
        ]);
        BreakRecord::create([
            'attendance_id' => $att->id,
            'break_start'   => '12:00',
            'break_end'     => '13:00',
        ]);

        // 修正申請を作成
        $req = RevisionRequest::create([
            'user_id'             => $user->id,
            'attendance_id'       => $att->id,
            'original_clock_in'   => '09:00',
            'original_clock_out'  => '18:00',
            'original_remarks'    => '元備考',
            'proposed_clock_in'   => '08:30',
            'proposed_clock_out'  => '17:30',
            'proposed_remarks'    => '修正理由',
            'breaks'              => json_encode([['start'=>'12:00','end'=>'13:00']]),
            'status'              => 'pending',
        ]);

        // 承認アクション実行
        $this->actingAs($admin)
             ->post(route('admin.revision.approve', $req->id))
             ->assertStatus(302);

        // DB の修正申請ステータスが approved になっている
        $this->assertDatabaseHas('revision_requests', [
            'id'     => $req->id,
            'status' => 'approved',
        ]);

        // Attendance が更新されている
        $this->assertDatabaseHas('attendances', [
            'id'        => $att->id,
            'clock_in'  => '08:30',
            'clock_out' => '17:30',
            'remarks'   => '修正理由',
        ]);
    }
}
