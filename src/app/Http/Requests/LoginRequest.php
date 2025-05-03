<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;                // 誰でも通す
    }

    public function rules()
    {
        return [
            'email'    => ['required','email'],
            'password' => ['required','string','min:8'],
        ];
    }

    /* 任意：項目名を日本語にしたい場合 */
    public function attributes()
    {
        return [
            'email'    => 'メールアドレス',
            'password' => 'パスワード',
        ];
    }

    public function messages(): array
{
    return [
        'email.required'    => 'メールアドレスを入力してください',
        'password.required' => 'パスワードを入力してください',
    ];
}
}
