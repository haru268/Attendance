<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RevisionRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'clock_in'  => ['required','date_format:H:i'],
            'clock_out' => ['required','date_format:H:i','after:clock_in'],
            'remarks'   => ['required','string'],
        ];
    }

    public function attributes()
    {
        return [
            'clock_in' => '出勤時間',
            'clock_out'=> '退勤時間',
            'remarks'  => '備考',
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required'   => '出勤時間を入力してください',
            'clock_out.after'     => '退勤時間は出勤時間より後にしてください',
            'remarks.required'    => '備考を記入してください',
        ];
    }
}
