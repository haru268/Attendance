<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 退勤打刻が記録される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤
        $this->post(route('attendance.clock'), ['type' => 'clock_in']);

        // 退勤
        $response = $this->post(route('attendance.clock'), ['type' => 'clock_out']);
        $response->assertStatus(302);

        $this->assertDatabaseHas('attendances', [
            'user_id'   => $user->id,
            'clock_out' => now()->format('H:i'),
        ]);
    }
}
