<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Http\Controllers\AttendanceController;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\AttendanceRequest;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\AdminAttendanceDetailRequest;
use App\Http\Requests\AdminRevisionRequest;

// Public routes: register & login
Route::get('/register', fn() => view('register'))->name('register.form');
Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'store'])->name('register');
Route::get('/login', fn() => view('login'))->name('login.form');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login');

// Protected routes
Route::middleware('auth')->group(function () {

    // 1) 打刻トップ
    Route::get('/attendance', [AttendanceController::class, 'index'])
         ->name('attendance');

    // 2) 打刻POST
    Route::post('/attendance/clock', [AttendanceController::class, 'clock'])
         ->name('attendance.clock');

    // 3) 月次一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
         ->name('attendance.list');

    // 4) 詳細 (YYYY-MM-DD or numeric ID)
    Route::get('/attendance/{key}', [AttendanceController::class, 'detail'])
         ->where('key', '[0-9]{4}-[0-9]{2}-[0-9]{2}|[0-9]+')
         ->name('attendance.detail');

    // 5) 修正申請 PATCH
    Route::patch('/attendance/update/{id}', [AttendanceController::class, 'update'])
         ->name('attendance.update');

    // Logout
    Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])
         ->name('logout');
});


/*==================================================
  ◆ 管理者ログイン
==================================================*/

// 管理者ログインフォーム
Route::get('/admin/login', fn () => view('admin_login'))
    ->name('admin.login.form');

// 管理者ログイン処理
Route::post('/admin/login', function (AdminLoginRequest $req) {
    if (Auth::attempt($req->only('email', 'password'))) {
        if (!Auth::user()->is_admin) {
            Auth::logout();
            return back()->withErrors(['email' => '管理者アカウントのみログインできます。'])->withInput();
        }
        return redirect()->route('admin.revision.list');
    }
    return back()->withErrors(['email' => 'ログイン情報が登録されていません'])->withInput();
})->name('admin.login');

/*==================================================
  ◆ 管理者専用エリア
==================================================*/
Route::prefix('admin')->middleware('auth')->group(function () {

    /*===== 1) 日次勤怠一覧（ダミー） =====*/
    Route::get('/attendance/list', function () {
        $sel      = Carbon::parse(request('date', now()->toDateString()));
        $today    = Carbon::today();

        $prevDate = $sel->copy()->subDay()->toDateString();
        $nextDate = $sel->copy()->addDay()->toDateString();

        $attendances = $sel->gt($today)
            ? []
            : collect(['山田 太郎', '西 伶奈', '増田 一世', '山本 敬吉', '秋田 朋美', '中西 教夫'])
                ->map(fn ($n) => (object)[
                    'id'         => $sel->format('Ymd') . $n,
                    'created_at' => $sel,
                    'clockIn'    => '09:00',
                    'clockOut'   => '18:00',
                    'breakTime'  => '1:00',
                    'totalTime'  => '8:00',
                    'user'       => (object)['name' => $n],
                ]);

        return view('admin_attendance_list', [
            'attendances'         => $attendances,
            'prevDate'            => $prevDate,
            'nextDate'            => $nextDate,
            'currentDateDisplay'  => $sel->format('Y/m/d'),
        ]);
    })->name('admin.attendance.list');

    /*===== 2) 勤怠詳細（ダミー） =====*/
    Route::get('/attendance/detail/{id}', function ($id) {
        return view('admin_attendance_detail', [
            'detail' => (object)[
                'id'       => $id,
                'date'     => Carbon::today()->toDateString(),
                'clockIn'  => '09:00',
                'clockOut' => '18:00',
                'breaks'   => [['start' => '12:00', 'end' => '12:30']],
                'remarks'  => '特になし',
                'user'     => (object)['name' => '山田 太郎'],
            ],
        ]);
    })->name('admin.attendance.detail');

    Route::patch('/attendance/detail/{id}', fn (AdminAttendanceDetailRequest $r, $id) =>
        back()->with('success', '保存！（ダミー）')
    )->name('admin.attendance.detail.update');

    /*===== 3) スタッフ一覧（ダミー） =====*/
    Route::get('/staff/list', function () {
        return view('admin_staff_list', [
            'staff' => collect([
                ['id' => 1, 'name' => '山田 太郎', 'email' => 'yamada@example.com'],
                ['id' => 2, 'name' => '西 伶奈', 'email' => 'reina@example.com'],
                ['id' => 3, 'name' => '増田 一世', 'email' => 'masuda@example.com'],
                ['id' => 4, 'name' => '山本 敬吉', 'email' => 'yamamoto@example.com'],
                ['id' => 5, 'name' => '秋田 朋美', 'email' => 'akita@example.com'],
                ['id' => 6, 'name' => '中西 教夫', 'email' => 'nakanishi@example.com'],
            ])->map(fn ($a) => (object)$a),
        ]);
    })->name('admin.staff.list');

    /*===== 4) スタッフ別月次勤怠一覧（ダミー） =====*/
    Route::get('/attendance/staff/{id}', function ($id) {
        $monthTop = Carbon::parse(request('date', now()->startOfMonth()));
        $prevDate = $monthTop->copy()->subMonth()->format('Y-m-01');
        $nextDate = $monthTop->copy()->addMonth()->format('Y-m-01');

        $staff = (object)[
            'id'   => $id,
            'name' => ['','山田 太郎','西 伶奈','増田 一世','山本 敬吉','秋田 朋美','中西 教夫'][$id] ?? '不明',
        ];

        $attendances = [];
        for ($d = $monthTop->copy(); $d->lte($monthTop->copy()->endOfMonth()); $d->addDay()) {
            $attendances[] = (object)[
                'id'         => $d->format('Ymd'),
                'created_at' => $d->copy(),
                'clockIn'    => '09:00',
                'clockOut'   => '18:00',
                'breakTime'  => '1:00',
                'totalTime'  => '8:00',
                'user'       => $staff,
            ];
        }

        return view('admin_attendance_staff', [
            'staff'               => $staff,
            'attendances'         => $attendances,
            'prevDate'            => $prevDate,
            'nextDate'            => $nextDate,
            'currentDateDisplay'  => $monthTop->format('Y/m'),
        ]);
    })->name('admin.attendance.staff');

    /* =========================================
   ■ 修正申請一覧（一般／管理者 共通パス）
   ========================================= */
Route::get('/stamp_correction_request/list', function (Request $request) {

    /* ――― 共通変数 ――― */
    $status   = $request->query('status', 'pending');
    $today    = Carbon::today();
    $user     = Auth::user();          // 便利なので変数へ
    $isAdmin  = $user->is_admin;
    $isDummy  = $user->is_dummy;       // ★ 追加：フラグを見る

    /* ───────── 管理者表示 ───────── */
    if ($isAdmin) {
        /* ダミーだけで OK なので従来通り */
        /* …ここは今までのダミー生成ロジックのまま … */

        return view('stamp_correction_request_list', [
            'revisionRequests' => $revisionRequests,
            'status'           => $status,
        ]);
    }

    /* ───────── 一般ユーザー表示 ───────── */

    if ($isDummy) {
        /* ダミーユーザー：いままでのハードコードで見せる */
        $revisionRequests = collect(range(1,5))->map(function ($i) {
            /* …省略（いままでのダミーデータ生成）… */
        })->all();
    } else {
        /* 実ユーザー：DB から自分の申請だけを取得 */
        $revisionRequests = \App\Models\RevisionRequest::where('user_id', $user->id)
                           ->where('status', $status === 'approved' ? '承認済み' : '承認待ち')
                           ->orderByDesc('created_at')
                           ->get();
    }

    return view('stamp_correction_request_list', [
        'revisionRequests' => $revisionRequests,
        'status'           => $status,
    ]);
})->name('stamp_correction_request.list');


    /*===== 6) 修正申請承認（ダミー） =====*/
    Route::post('/stamp_correction_request/approve/{id}', fn (AdminRevisionRequest $r, $id) =>
        back()->with('success', '承認しました。')
    )->name('admin.revision.approve');

    /*===== 7) 管理者ログアウト =====*/
    Route::post('/logout', fn () => tap(Auth::logout(), fn () => session()->invalidate()) ?: redirect('/admin/login'))
        ->name('admin.logout');
});
