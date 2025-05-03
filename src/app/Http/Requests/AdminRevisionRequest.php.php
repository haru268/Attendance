<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminRevisionRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'date'          => ['required','date'],
            'clock_in'      => ['required','date_format:H:i'],
            'clock_out'     => ['required','date_format:H:i','after:clock_in'],
            'breaks.*.start'=> ['nullable','date_format:H:i'],
            'breaks.*.end'  => ['nullable','date_format:H:i','after:breaks.*.start'],
            'remarks'       => ['required','string'],
            'comment'       => ['required','string','max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'date'       => '日付',
            'clock_in'   => '出勤時間',
            'clock_out'  => '退勤時間',
            'remarks'    => '備考',
            'comment'    => '承認コメント',
        ];
    }

    

}
