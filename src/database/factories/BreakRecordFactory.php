<?php

namespace Database\Factories;

use App\Models\BreakRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakRecordFactory extends Factory
{
    protected $model = BreakRecord::class;

    public function definition()
    {
        // 勤怠に紐づくとき刻みはシーダー側で決めるのでここは固定でもOK
        return [
            'break_start' => '12:00:00',
            'break_end'   => '12:30:00',
        ];
    }
}
