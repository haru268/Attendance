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
        $dummies = [
            ['山田　太郎','yamada@example.com'],
            ['西　伶奈'  ,'reina@example.com'],
            ['増田　一世','masuda@example.com'],
            ['山本　敬吉','yamamoto@example.com'],
            ['秋田　朋美','akita@example.com'],
            ['中西　教夫','nakanishi@example.com'],
        ];

        foreach ($dummies as [$name, $email]) {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name'              => $name,
                    'password'          => Hash::make('password'),
                    'is_admin'          => false,
                    'is_dummy'          => true,
                    'email_verified_at' => now(),
                ]
            );

          
            if ($user->wasRecentlyCreated) {
                $start = Carbon::today()->subDays(29);
                for ($d = $start->copy(); $d->lte(Carbon::today()); $d->addDay()) {
                    $att = Attendance::create([
                        'user_id'   => $user->id,
                        'date'      => $d->toDateString(),
                        'clock_in'  => '09:00',
                        'clock_out' => '18:00',
                        'remarks'   => null,
                    ]);
                    $att->breakRecords()->create([
                        'break_start' => '12:00',
                        'break_end'   => '13:00',
                    ]);
                }
            }
        }
    }
}