<?php
// app/Providers/FortifyServiceProvider.php
namespace App\Providers;

use Laravel\Fortify\Fortify;
use Illuminate\Support\ServiceProvider;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\UpdateUserPassword;          // ★ 追加
use App\Actions\Fortify\ResetUserPassword;           // これも後で作る
use App\Actions\Fortify\UpdateUserProfileInformation;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);      // ★ これを追記
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);        // 同様に
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
    }
}
