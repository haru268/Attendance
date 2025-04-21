<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakRecord;
use App\Models\RevisionRequest;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 管理者ユーザー
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

        // その他ユーザー
        User::factory(5)->create();

        // 一般ユーザーの過去30日分の勤怠を生成
        Attendance::factory()
            ->count(30)
            ->for($general, 'user')
            ->has(BreakRecord::factory()->count(1), 'breakRecords')
            ->create()
            ->each(function (Attendance $attendance) {
                // 各勤怠に対して 1 件の修正申請を作成
                RevisionRequest::factory()
                    ->for($attendance->user, 'user')
                    ->for($attendance, 'attendance')
                    ->create();
            });
    }
}
