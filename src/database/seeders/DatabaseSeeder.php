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
       
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'              => 'Admin User',
                'password'          => Hash::make('password'),
                'is_admin'          => true,
                'is_dummy'          => false,
                'email_verified_at' => now(),
            ]
        );

      
        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name'              => 'Sample User',
                'password'          => Hash::make('password'),
                'is_admin'          => false,
                'is_dummy'          => false,
                'email_verified_at' => now(),
            ]
        );

       
        $attUser = User::updateOrCreate(
            ['email' => 'seeduser@example.com'],
            [
                'name'              => 'Seed User',
                'password'          => Hash::make('password'),
                'is_admin'          => false,
                'is_dummy'          => true,
                'email_verified_at' => now(),
            ]
        );
        if ($attUser->wasRecentlyCreated) {
            $this->seedAttendanceFor($attUser);
        }

     
        $this->seedDummyUsers();

      
        $pendingDate = Carbon::today()->subDays(2)->toDateString();
        $pendingAttId = Attendance::where('user_id', $attUser->id)
                                   ->where('date', $pendingDate)
                                   ->value('id');
        RevisionRequest::updateOrCreate(
            [
                'user_id'       => $attUser->id,
                'attendance_id' => $pendingAttId,
            ],
            [
                'original_clock_in'   => '09:00',
                'original_clock_out'  => '18:00',
                'proposed_clock_in'   => '09:30',
                'proposed_clock_out'  => '17:30',
                'original_remarks'    => '元の備考',
                'proposed_remarks'    => '打刻ミス',
                'breaks'              => json_encode([['start'=>'12:00','end'=>'13:00']]),
                'status'              => 'pending',
            ]
        );

    
        $approvedDate = Carbon::today()->subDays(5)->toDateString();
        $approvedAttId = Attendance::where('user_id', $attUser->id)
                                     ->where('date', $approvedDate)
                                     ->value('id');
        RevisionRequest::updateOrCreate(
            [
                'user_id'       => $attUser->id,
                'attendance_id' => $approvedAttId,
            ],
            [
                'original_clock_in'   => '09:00',
                'original_clock_out'  => '18:00',
                'proposed_clock_in'   => '08:45',
                'proposed_clock_out'  => '17:15',
                'original_remarks'    => '元の備考',
                'proposed_remarks'    => '打刻ミス',
                'breaks'              => json_encode([['start'=>'12:10','end'=>'12:50']]),
                'status'              => 'approved',
            ]
        );
    }

 
    private function seedAttendanceFor(User $user): void
    {
        $start = Carbon::today()->subDays(29);

        for ($d = $start->copy(); $d->lte(Carbon::today()); $d->addDay()) {
            $att = Attendance::create([
                'user_id'   => $user->id,
                'date'      => $d->toDateString(),
                'clock_in'  => '09:00',
                'clock_out' => '18:00',
                'remarks'   => null,
            ]);

            BreakRecord::create([
                'attendance_id' => $att->id,
                'break_start'   => '12:00',
                'break_end'     => '13:00',
            ]);
        }
    }

   
    private function seedDummyUsers(): void
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
                $this->seedAttendanceFor($user);
            }
        }
    }
}

