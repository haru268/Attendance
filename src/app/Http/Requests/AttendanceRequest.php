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
        // 時間フォーマットの正規表現 (0～23時、0～59分)
        $timeRegex = ['regex:/^([01]?\d|2[0-3]):[0-5]\d$/'];

        return [
            // 出勤・退勤は必須＋フォーマット＋順序チェック
            'clock_in'  => array_merge(['required'], $timeRegex),
            'clock_out' => array_merge(['required'], $timeRegex, ['after_or_equal:clock_in']),

            // 休憩(開始・終了)は任意＋フォーマット＋勤務時間内チェック
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

            // 備考は必須
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
            // 出勤／退勤：形式 or 順序エラー → 同じ文言
            'clock_in.required'        => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_in.regex'           => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required'       => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.regex'          => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',

            // 休憩：勤務時間外チェック → 同じ文言
            'breaks.*.start.regex'       => '休憩時間が勤務時間外です',
            'breaks.*.start.after_or_equal'   => '休憩時間が勤務時間外です',
            'breaks.*.start.before_or_equal'  => '休憩時間が勤務時間外です',

            'breaks.*.end.regex'         => '休憩時間が勤務時間外です',
            'breaks.*.end.after_or_equal'     => '休憩時間が勤務時間外です',
            'breaks.*.end.before_or_equal'    => '休憩時間が勤務時間外です',

            // 備考
            'remarks.required'         => '備考を記入してください',
        ];
    }
}
