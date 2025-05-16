<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DummyUsersAndAttendancesSeeder extends Seeder
{
    public function run()
    {
        // １．ダミー６名の基本情報
        $names = [
            '山田 太郎', '西 伶奈', '増田 一世',
            '山本 敬吉', '秋田 朋美', '中西 教夫',
        ];

        foreach ($names as $i => $name) {
            // スペースを _ にしてメール生成
            $email = str_replace(' ', '_', $name).'@example.com';

            // まだなければ作成
            $user = User::firstOrCreate([
                'email' => $email,
            ], [
                'name'     => $name,
                'password' => Hash::make('password'),
            ]);

            // 今日の日付でレコードがなければ作成
            Attendance::firstOrCreate(
                ['user_id' => $user->id, 'date' => Carbon::today()->toDateString()],
                [
                    'clock_in'  => '09:00',
                    'clock_out' => '18:00',
                ]
            );
        }
    }
}
