<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    /**
     * ユーザーのパスワードを更新する
     *
     * @param  \App\Models\User  $user
     * @param  array             $input
     * @return void
     */
    public function update($user, array $input)
    {
        /* ---------- バリデーション ---------- */
        Validator::make($input, [
            'current_password'      => ['required', 'current_password'],
            'password'              => [
                'required', 'confirmed',
                Password::min(8)         // ★ 8文字以上
                        ->letters()       // 英字
                        ->numbers()       // 数字
            ],
        ], [
            /* === エラーメッセージ === */
            'current_password.required'       => '現在のパスワードを入力してください',
            'current_password.current_password'=> '現在のパスワードが正しくありません',
            'password.required'               => 'パスワードを入力してください',
            'password.confirmed'              => 'パスワードと一致しません',
            'password.min'                    => 'パスワードは8文字以上で入力してください',
        ])->validate();

        /* ---------- 保存 ---------- */
        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}
