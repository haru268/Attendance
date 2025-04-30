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
        User::factory()->create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $general = User::factory()->create([
            'name'  => 'General User',
            'email' => 'general@example.com',
        ]);

        User::factory(5)->create();

        Attendance::factory()
            ->count(30)
            ->for($general, 'user')
            ->has(BreakRecord::factory()->count(1), 'breakRecords')
            ->create()
            ->each(function (Attendance $attendance) {
                RevisionRequest::factory()
                    ->for($attendance->user, 'user')
                    ->for($attendance, 'attendance')
                    ->create();
            });
    }
}
