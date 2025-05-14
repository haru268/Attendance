<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakRecord;
use App\Models\RevisionRequest;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /* ───────── 管理者 ───────── */
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'is_dummy' => false,
            ]
        );

        /* ───────── ダミー一般ユーザー 6 名 ───────── */
        $dummies = [
            ['山田　太郎','yamada@example.com'],
            ['西　伶奈'  ,'reina@example.com' ],
            ['増田　一世','masuda@example.com'],
            ['山本　敬吉','yamamoto@example.com'],
            ['秋田　朋美','akita@example.com' ],
            ['中西　教夫','nakanishi@example.com'],
        ];

        foreach ($dummies as [$name, $email]) {

            /** @var \App\Models\User $user */
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name'     => $name,
                    'password' => Hash::make('password'),
                    'is_dummy' => true,
                    'is_admin' => false,
                ]
            );

            /* 新規作成されたときだけダミー勤怠を投入 */
            if ($user->wasRecentlyCreated) {
                $this->seedAttendanceFor($user);
            }
        }
    }

    /********************************************************
     * 指定ユーザーに直近 30 日分のダミー勤怠データを作成
     ********************************************************/
    private function seedAttendanceFor(User $user): void
    {
        $start = Carbon::today()->subDays(29);

        for ($d = $start->copy(); $d->lte(Carbon::today()); $d->addDay()) {

            /** @var Attendance $att */
            $att = Attendance::factory()
                ->for($user)
                ->state([
                    'clock_in'   => '09:00',
                    'clock_out'  => '18:00',
                    'created_at' => $d,
                    'updated_at' => $d,
                ])
                ->create();

            BreakRecord::factory()
                ->for($att)
                ->create();

            if (random_int(1, 100) <= 5) {
                RevisionRequest::factory()
                    ->for($user)
                    ->for($att)
                    ->create();
            }
        }
    }
}
