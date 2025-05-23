<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤打刻が記録される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤
        $response = $this->post(route('attendance.clock'), ['type' => 'clock_in']);
        $response->assertStatus(302);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date'    => now()->toDateString(),
        ]);
    }
}
