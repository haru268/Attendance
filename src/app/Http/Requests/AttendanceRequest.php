<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    /**
     * 認可（常に許可）
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * バリデーションルール
     *
     * @return array
     */
    public function rules()
    {
        $timeRegex = ['regex:/^([01]?\d|2[0-3]):[0-5]\d$/'];

        return [
            'clock_in'  => array_merge(['required'], $timeRegex),
            'clock_out' => array_merge(['required'], $timeRegex, ['after_or_equal:clock_in']),

            'breaks.*.start' => array_merge(
                ['nullable'],
                $timeRegex,
                ['after_or_equal:clock_in', 'before_or_equal:clock_out']
            ),
            'breaks.*.end'   => array_merge(
                ['nullable'],
                $timeRegex,
                ['after_or_equal:clock_in', 'before_or_equal:clock_out']
            ),

            'remarks'   => ['required'],
        ];
    }

    /**
     * 日本語エラーメッセージ
     *
     * @return array
     */
    public function messages()
    {
        return [

            'clock_in.required'        => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_in.regex'           => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required'       => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.regex'          => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',

            'breaks.*.start.regex'       => '休憩時間が勤務時間外です',
            'breaks.*.start.after_or_equal'   => '休憩時間が勤務時間外です',
            'breaks.*.start.before_or_equal'  => '休憩時間が勤務時間外です',

            'breaks.*.end.regex'         => '休憩時間が勤務時間外です',
            'breaks.*.end.after_or_equal'     => '休憩時間が勤務時間外です',
            'breaks.*.end.before_or_equal'    => '休憩時間が勤務時間外です',

            'remarks.required'         => '備考を記入してください',
        ];
    }
}
