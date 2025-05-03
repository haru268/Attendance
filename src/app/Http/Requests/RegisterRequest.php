<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'name'              => 'required|string',
            'email'             => 'required|email',
            'password'          => 'required|confirmed|min:8',
            // ↑ confirmed … `password_confirmation` と一致チェック
        ];
    }

    public function messages()
    {
        return [
            'name.required'     => 'お名前を入力してください',
            'email.required'    => 'メールアドレスを入力してください',
            'email.email'       => 'メールアドレスを正しく入力してください',
            'password.required' => 'パスワードを入力してください',
            'password.confirmed'=> 'パスワードと一致しません',
            'password.min'      => 'パスワードは8文字以上で入力してください',
        ];
    }
}
