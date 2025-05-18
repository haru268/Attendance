<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;          
use Carbon\Carbon;

class DummyAttendanceSeeder extends Seeder
{
    public function run(): void
    {
      
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

       
        $att->breaks()->delete();        
        $att->breaks()->create([
            'start' => '12:00',
            'end'   => '12:45',
        ]);
    }
}
