<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakRecord;
use App\Models\RevisionRequest;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者
        User::factory()->create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        // 一般ユーザー
        $general = User::factory()->create([
            'name'  => 'General User',
            'email' => 'general@example.com',
        ]);

        // ダミーユーザー
        User::factory(5)->create();

        // 勤怠＋休憩１回だけ
        Attendance::factory()
            ->count(30)
            ->for($general, 'user')
            ->has(BreakRecord::factory()->count(1), 'breakRecords') // 休憩１回
            ->create()
            ->each(function (Attendance $attendance) {
                RevisionRequest::factory()
                    ->for($attendance->user, 'user')
                    ->for($attendance, 'attendance')
                    ->create();
            });
    }
}
