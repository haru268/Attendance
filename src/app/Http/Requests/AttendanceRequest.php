<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    public function authorize()
    {
        // 認証済みユーザーのみ許可する場合は true のままにします
        return true;
    }

    public function rules()
    {
        return [
            // 出勤・退勤
            'clockIn'       => 'required|date_format:H:i|before:clockOut',
            'clockOut'      => 'required|date_format:H:i|after:clockIn',

            // 休憩開始・終了（配列で受け取る想定）
            'breakStart.*'  => 'nullable|date_format:H:i|after_or_equal:clockIn|before_or_equal:clockOut',
            'breakEnd.*'    => 'nullable|date_format:H:i|after_or_equal:breakStart.*|before_or_equal:clockOut',

            // 備考
            'remarks'       => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            // 出勤／退勤
            'clockIn.required'       => '出勤時間もしくは退勤時間が不適切な値です。',
            'clockIn.date_format'    => '出勤時間もしくは退勤時間が不適切な値です。',
            'clockOut.required'      => '出勤時間もしくは退勤時間が不適切な値です。',
            'clockOut.date_format'   => '出勤時間もしくは退勤時間が不適切な値です。',
            'clockOut.after'         => '出勤時間もしくは退勤時間が不適切な値です。',

            // 休憩
            'breakStart.*.date_format'     => '休憩時間が勤務時間外です。',
            'breakStart.*.after_or_equal'  => '休憩時間が勤務時間外です。',
            'breakStart.*.before_or_equal' => '休憩時間が勤務時間外です。',
            'breakEnd.*.date_format'       => '休憩時間が勤務時間外です。',
            'breakEnd.*.after_or_equal'    => '休憩時間が勤務時間外です。',
            'breakEnd.*.before_or_equal'   => '休憩時間が勤務時間外です。',

            // 備考
            'remarks.required'      => '備考を記入してください。',
            'remarks.string'        => '備考は文字列で入力してください。',
            'remarks.max'           => '備考は255文字以内で入力してください。',
        ];
    }
}
