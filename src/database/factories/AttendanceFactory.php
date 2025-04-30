<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        $date = $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            'clock_in'   => $date->format('H:i:s'),
            'clock_out'  => $date->modify('+9 hours')->format('H:i:s'),
            'remarks'    => $this->faker->sentence(),
            'created_at' => $date,
            'updated_at' => $date,
        ];
    }
}
