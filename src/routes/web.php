<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Controllers\AttendanceController;
use App\Models\Attendance;
use App\Models\RevisionRequest;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\AttendanceRequest;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\AdminRevisionRequest;

/*
|--------------------------------------------------------------------------
| Public routes: register & login
|--------------------------------------------------------------------------
*/

// 会員登録フォーム
Route::get('/register', fn() => view('register'))
     ->name('register.form');

// 会員登録処理
Route::post('/register', function(RegisterRequest $request) {
    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => bcrypt($request->password),
    ]);
    Auth::login($user);
    return redirect()->route('attendance')->with('success','登録が完了しました。');
})->name('register');

// ログインフォーム
Route::get('/login', fn() => view('login'))
     ->name('login.form');

// ログイン処理
Route::post('/login', function(LoginRequest $request) {
    if (Auth::attempt($request->only('email','password'))) {
        // 管理者はここで弾く
        if (Auth::user()->is_admin) {
            Auth::logout();
            return back()
                ->withErrors(['email'=>'一般ユーザー用のアカウントでログインしてください。'])
                ->withInput();
        }
        return redirect()->route('attendance');
    }
    return back()->withErrors(['email'=>'認証に失敗しました。'])->withInput();
})->name('login');

/*
|--------------------------------------------------------------------------
| Protected routes (normal users)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // 打刻トップ
    Route::get('/attendance', [AttendanceController::class,'index'])
         ->name('attendance');

    // 打刻 POST
    Route::post('/attendance/clock', [AttendanceController::class,'clock'])
         ->name('attendance.clock');

    // 月次勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class,'list'])
         ->name('attendance.list');

    // 勤怠詳細 (YYYY-MM-DD or numeric ID)
    Route::get('/attendance/{key}', [AttendanceController::class,'detail'])
         ->where('key','[0-9]{4}-[0-9]{2}-[0-9]{2}|[0-9]+')
         ->name('attendance.detail');

    // 修正申請 (PATCH)
    Route::patch('/attendance/update/{id}', [AttendanceController::class,'update'])
         ->name('attendance.update');

    // 自分の修正申請一覧
    Route::get('/stamp_correction_request/list', function(Request $request) {
        $status = $request->query('status','pending');
        $user   = Auth::user();
        $revisionRequests = RevisionRequest::where('user_id',$user->id)
                                           ->where('status',$status)
                                           ->orderByDesc('created_at')
                                           ->get();
        return view('stamp_correction_request_list', compact('revisionRequests','status'));
    })->name('stamp_correction_request.list');

    // ログアウト
    Route::post('/logout', function() {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});

/*
|--------------------------------------------------------------------------
| Admin login
|--------------------------------------------------------------------------
*/

// 管理者ログインフォーム
Route::get('/admin/login', fn() => view('admin_login'))
     ->name('admin.login.form');

// 管理者ログイン処理
Route::post('/admin/login', function(AdminLoginRequest $req) {
    if (Auth::attempt($req->only('email','password'))) {
        if (! Auth::user()->is_admin) {
            Auth::logout();
            return back()
                ->withErrors(['email'=>'管理者アカウントのみログインできます。'])
                ->withInput();
        }
        return redirect()->route('admin.attendance.list');
    }
    return back()->withErrors(['email'=>'ログイン情報が登録されていません'])->withInput();
})->name('admin.login');

/*
|--------------------------------------------------------------------------
| Protected routes (admin users)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware('auth')->group(function () {

    // 1) 管理者：日次勤怠一覧
    Route::get('/attendance/list', function(Request $request) {
        // 表示対象日を Carbon で取得
        $sel      = Carbon::parse($request->query('date', now()->toDateString()));
        // 今日も Carbon として
        $today    = Carbon::today();
        $prevDate = $sel->copy()->subDay()->toDateString();
        $nextDate = $sel->copy()->addDay()->toDateString();

        // strictly after today → 何も表示しない
        if ($sel->gt($today)) {
            $attendances = collect();
        } else {
            // 管理者でない全ユーザー
            $users = User::where('is_admin', false)->get();
            $attendances = collect();

            foreach ($users as $user) {
                if ($user->is_dummy) {
                    // ダミーユーザーは常に出す
                    $attendances->push((object)[
                        'id'         => null,
                        'user'       => $user,
                        'created_at' => $sel,
                        'clockIn'    => '09:00',
                        'clockOut'   => '18:00',
                        'breakTime'  => '1:00',
                        'totalTime'  => '08:00',
                    ]);
                    continue;
                }
                // 実ユーザー：登録日以前なら
                if ($user->created_at->toDateString() > $sel->toDateString()) {
                    continue;
                }
                // 該当日の Attendance
                $att = Attendance::with('breakRecords')
                    ->where('user_id', $user->id)
                    ->whereDate('date', $sel)
                    ->first();
                if (! $att) {
                    // レコードなければスキップ
                    continue;
                }
                $sec = $att->breakRecords->sum(fn($b)=>
                    strtotime($b->break_end ?: now()) - strtotime($b->break_start)
                );
                $workSec = ($att->clock_in && $att->clock_out)
                         ? strtotime($att->clock_out)
                           - strtotime($att->clock_in)
                           - $sec
                         : 0;
                $attendances->push((object)[
                    'id'         => $att->id,
                    'user'       => $user,
                    'created_at' => Carbon::parse($att->date),
                    'clockIn'    => optional($att->clock_in)->format('H:i')  ?: '-',
                    'clockOut'   => optional($att->clock_out)->format('H:i') ?: '-',
                    'breakTime'  => gmdate('H:i', $sec),
                    'totalTime'  => $att->clock_in && $att->clock_out
                                   ? gmdate('H:i', max(0, $workSec))
                                   : '-',
                ]);
            }
        }

        return view('admin_attendance_list', compact(
            'attendances','prevDate','nextDate'
        ))->with('currentDateDisplay', $sel->format('Y/m/d'));
    })->name('admin.attendance.list');

    // 2) 勤怠詳細（管理者も共通 detail メソッドを利用）
    Route::get('/attendance/detail/{key}', [AttendanceController::class,'detail'])
         ->where('key','[0-9]{4}-[0-9]{2}-[0-9]{2}|[0-9]+')
         ->name('admin.attendance.detail');

    // 3) スタッフ別月次勤怠一覧
    Route::get('/attendance/staff/{id}', [AttendanceController::class,'staffAttendance'])
         ->name('admin.attendance.staff');

    // 4) 修正申請承認（ダミー）
    Route::post('/stamp_correction_request/approve/{id}', fn(AdminRevisionRequest $r,$id)=>
        back()->with('success','承認しました。')
    )->name('admin.revision.approve');

    // 5) スタッフ一覧
    Route::get('/staff/list', function(){
        $dummyNames = ['山田 太郎','西 伶奈','増田 一世','山本 敬吉','秋田 朋美','中西 教夫'];
        $dummy = collect($dummyNames)->map(fn($n,$i) => (object)[
            'id'    => $i+1,
            'name'  => $n,
            'email' => Str::slug(Str::ascii($n),'_').'@example.com',
        ]);
        $real = User::where('is_admin',false)
                    ->get(['id','name','email'])
                    ->map(fn($u)=>(object)[
                        'id'=>$u->id,'name'=>$u->name,'email'=>$u->email,
                    ]);
        $realNames = $real->pluck('name')->map(fn($n)=>str_replace('　',' ',$n))->all();
        $dummy = $dummy->reject(fn($d)=> in_array(str_replace('　',' ',$d->name), $realNames));
        $staff = $dummy->concat($real);
        return view('admin_staff_list', compact('staff'));
    })->name('admin.staff.list');

    // 管理者ログアウト
    Route::post('/logout', function(){
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/admin/login');
    })->name('admin.logout');

});
