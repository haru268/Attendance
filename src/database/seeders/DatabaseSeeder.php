<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakRecord;
use App\Models\RevisionRequest;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者ユーザー
        User::factory()->create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        // 6 名のダミー一般ユーザー
        $names = [
            ['山田　太郎','yamada@example.com'],
            ['西　伶奈','reina@example.com'],
            ['増田　一世','masuda@example.com'],
            ['山本　敬吉','yamamoto@example.com'],
            ['秋田　朋美','akita@example.com'],
            ['中西　教夫','nakanishi@example.com'],
        ];

        foreach ($names as [$fullName, $email]) {
            $user = User::factory()->create([
                'name'     => $fullName,
                'email'    => $email,
                'password' => bcrypt('password'),
            ]);

            // 過去30日分の勤怠データ
            $start = Carbon::today()->subDays(29);
            for ($d = $start->copy(); $d->lte(Carbon::today()); $d->addDay()) {
                $attendance = Attendance::factory()
                    ->for($user, 'user')
                    ->state([
                        'clock_in'   => '09:00',
                        'clock_out'  => '18:00',

                        'created_at' => $d,
                        'updated_at' => $d,
                    ])
                    ->create();

                // 休憩 1 回
                BreakRecord::factory()
                    ->for($attendance, 'attendance')
                    ->create();

                // 5% の確率で修正申請を作成
                if (random_int(1, 100) <= 5) {
                    RevisionRequest::factory()
                        ->for($user, 'user')
                        ->for($attendance, 'attendance')
                        ->create();
                }
            }
        }
    }
}
