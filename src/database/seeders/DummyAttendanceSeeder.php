<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;           // ★モデル名は実際のものに合わせて
use Carbon\Carbon;

class DummyAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // 例：2025‑05‑03 の勤怠
        $att = Attendance::updateOrCreate(
            ['id' => '20250503'],
            [
                'user_id'   => 1,
                'date'      => Carbon::parse('2025‑05‑03'),
                'clock_in'  => '09:00',
                'clock_out' => '18:00',
                'remarks'   => '特になし',
            ]
        );

        // 休憩は 1 レコードだけ残す
        $att->breaks()->delete();           // 既存をリセット（あれば）
        $att->breaks()->create([
            'start' => '12:00',
            'end'   => '12:45',
        ]);
    }
}
