<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;           
    }

    public function rules()
    {
        return [
            'email'    => ['required','email'],
            'password' => ['required','string','min:8'],
        ];
    }

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
