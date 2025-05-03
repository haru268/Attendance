<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceDetailRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'date'               => ['required','date'],
            'clock_in'           => ['required','date_format:H:i'],
            'clock_out'          => ['required','date_format:H:i','after:clock_in'],
            'breaks.*.start'     => ['nullable','date_format:H:i','after_or_equal:clock_in'],
            'breaks.*.end'       => ['nullable','date_format:H:i','after:breaks.*.start','before_or_equal:clock_out'],
            'remarks'            => ['required','string'],
        ];
    }

    public function attributes()
    {
        return [
            'date'           => '日付',
            'clock_in'       => '出勤時間',
            'clock_out'      => '退勤時間',
            'breaks.*.start' => '休憩開始時間',
            'breaks.*.end'   => '休憩終了時間',
            'remarks'        => '備考',
        ];
    }
}
