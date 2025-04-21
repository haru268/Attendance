<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes (Dummy Mode)
|--------------------------------------------------------------------------
|
| すべてクロージャまたはダミーコントローラで実装しています。
| 実際のコントローラが用意できたら適宜差し替えてください。
|
*/

// ────────────────────────────────────────────────────
// ■ 一般ユーザー向けルート
// ────────────────────────────────────────────────────

// 会員登録フォーム
Route::get('/register', fn() => view('register'))
    ->name('register.form');

// 会員登録処理（ダミー：完了後ログイン画面へ）
Route::post('/register', fn() => redirect('/login')->with('success', '登録が完了しました。'))
    ->name('register');

// ログインフォーム
Route::get('/login', fn() => view('login'))
    ->name('login.form');

// ログイン処理（ダミー認証）
Route::post('/login', function () {
    $credentials = request(['email', 'password']);
    if (Auth::attempt($credentials)) {
        // 管理者アカウントなら弾く
        if (Auth::user()->is_admin) {
            Auth::logout();
            return back()->withErrors(['email' => '一般ユーザー用のアカウントでログインしてください。']);
        }
        return redirect()->route('attendance');
    }
    return back()->withErrors(['email' => '認証に失敗しました。']);
})->name('login');

// 認証済みユーザーのみアクセス可
Route::middleware('auth')->group(function () {

    // 打刻画面
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance');

    // 日次勤怠一覧（自分のダミーデータ）
    Route::get('/attendance/list', function (Request $request) {
        // 年・月パラメータ取得（なければ今月）
        $year  = $request->query('year',  Carbon::now()->format('Y'));
        $month = $request->query('month', Carbon::now()->format('m'));

        // 月初および今日
        $firstOfMonth = Carbon::create($year, $month, 1);
        $today        = Carbon::today();

        // 前月・翌月リンク用
        $prevDt = $firstOfMonth->copy()->subMonth();
        $nextDt = $firstOfMonth->copy()->addMonth();

        // 変更後：配列で定義
        $prev = [
            'year'  => $prevDt->format('Y'),
            'month' => $prevDt->format('m'),
        ];
        $next = [
            'year'  => $nextDt->format('Y'),
            'month' => $nextDt->format('m'),
        ];

        // 見た目用「YYYY/MM」
        $currentMonth = $firstOfMonth->format('Y/m');

        // 選択月が未来なら記録なしフラグ
        if ($firstOfMonth->gt($today->copy()->startOfMonth())) {
            $attendances = [];
            $noRecords   = true;
        } else {
            $noRecords = false;
            // 今月なら今日まで、過去月なら月末まで
            $end = ($firstOfMonth->year === $today->year && $firstOfMonth->month === $today->month)
                ? $today
                : $firstOfMonth->copy()->endOfMonth();

            $attendances = [];
            for ($d = $firstOfMonth->copy(); $d->lte($end); $d->addDay()) {
                $attendances[] = (object)[
                    'date'      => $d->copy(),
                    'clockIn'   => '09:00',
                    'clockOut'  => '18:00',
                    'breakTime' => '1:00',
                    'totalTime' => '8:00',
                ];
            }
        }

        return view('attendance_list', compact(
            'attendances',
            'prev', 'next',
            'currentMonth',
            'noRecords'
        ));
    })->name('attendance.list');

    // 勤怠詳細（自分のダミーデータ）
    Route::get('/attendance/{id}', [AttendanceController::class, 'detail'])
        ->name('attendance.detail');

    // 修正申請送信（常に「承認待ち」ダミー）
    Route::post('/attendance/update/{id}', [AttendanceController::class, 'update'])
        ->name('attendance.update');

    // 修正申請一覧（一般ユーザー）
    Route::get('/stamp_correction_request/list', function () {
        $revisionRequests = [
            (object)[
                'id'             => 1,
                'status'         => '承認待ち',
                'targetDatetime' => '2025-04-09 09:00〜18:00',
                'reason'         => '打刻ミス',
                'created_at'     => Carbon::today()->subDay(),
            ],
        ];
        return view('stamp_correction_request_list', compact('revisionRequests'));
    })->name('stamp_correction_request.list');

    // ログアウト
    Route::get('/logout', fn() => Auth::logout() ?: redirect('/login'))
        ->name('logout');
});

// ────────────────────────────────────────────────────
// ■ 管理者向けルート
// ────────────────────────────────────────────────────

// 管理者ログインフォーム
Route::get('/admin/login', fn() => view('admin_login'))
    ->name('admin.login.form');

// 管理者ログイン処理（ダミー認証）
Route::post('/admin/login', function () {
    $credentials = request(['email', 'password']);
    if (Auth::attempt($credentials)) {
        if (! Auth::user()->is_admin) {
            Auth::logout();
            return back()->withErrors(['email' => '管理者アカウントのみログインできます。']);
        }
        return redirect()->route('admin.attendance.list');
    }
    return back()->withErrors(['email' => '管理者認証に失敗しました。']);
})->name('admin.login');

// 認証済み管理者のみアクセス可
Route::middleware('auth')->group(function () {

    // 日次勤怠一覧（管理者）
    Route::get('/admin/attendance/list', function () {
        $date            = request('date', Carbon::today()->format('Y-m-d'));
        $sel             = Carbon::parse($date);
        $prevDate        = $sel->copy()->subDay()->format('Y-m-d');
        $nextDate        = $sel->copy()->addDay()->format('Y-m-d');
        $attendances     = [];

        if ($sel->lte(Carbon::today())) {
            $names = ['山田太郎','西伶奈','増田一世','山本敬吉','秋田朋美','中西教夫'];
            foreach ($names as $i => $n) {
                $attendances[] = (object)[
                    'id'         => $i + 1,
                    'created_at' => $sel,
                    'clockIn'    => '09:00',
                    'clockOut'   => '18:00',
                    'breakTime'  => '1:00',
                    'totalTime'  => '8:00',
                    'user'       => (object)['name' => $n],
                ];
            }
        }

        $currentDateDisplay = $sel->format('Y/m/d');
        return view('admin_attendance_list', compact('attendances','prevDate','nextDate','currentDateDisplay'));
    })->name('admin.attendance.list');

    // 勤怠詳細（管理者編集）
    Route::get('/admin/attendance/detail/{id}', function ($id) {
        $detail = (object)[
            'id'       => $id,
            'date'     => Carbon::today()->format('Y-m-d'),
            'clockIn'  => '09:00',
            'clockOut' => '18:00',
            'breaks'   => [['start' => '12:00', 'end' => '12:30']],
            'remarks'  => '特になし',
            'user'     => (object)['name' => '山田太郎'],
        ];
        return view('admin_attendance_detail', compact('detail'));
    })->name('admin.attendance.detail');

    // スタッフ一覧（管理者）
    Route::get('/admin/staff/list', function () {
        $staff = [
            (object)['id'=>1,'name'=>'山田太郎','email'=>'yamada@example.com'],
            (object)['id'=>2,'name'=>'西伶奈','email'=>'reina@example.com'],
            (object)['id'=>3,'name'=>'増田一世','email'=>'masuda@example.com'],
            (object)['id'=>4,'name'=>'山本敬吉','email'=>'yamamoto@example.com'],
            (object)['id'=>5,'name'=>'秋田朋美','email'=>'akita@example.com'],
            (object)['id'=>6,'name'=>'中西教夫','email'=>'nakanishi@example.com'],
        ];
        return view('admin_staff_list', compact('staff'));
    })->name('admin.staff.list');

    // スタッフ別勤怠一覧（管理者）
    Route::get('/admin/attendance/staff/{id}', function ($id) {
        $map      = [1=>'山田太郎',2=>'西伶奈',3=>'増田一世',4=>'山本敬吉',5=>'秋田朋美',6=>'中西教夫'];
        $staff    = (object)['id'=>$id,'name'=>$map[$id] ?? '不明'];
        $today    = Carbon::today();
        $start    = $today->copy()->startOfMonth();
        $attendances = [];

        for ($d = $start; $d->lte($today); $d->addDay()) {
            $attendances[] = (object)[
                'id'         => $d->day,
                'created_at' => $d,
                'clockIn'    => '09:00',
                'clockOut'   => '18:00',
                'breakTime'  => '1:00',
                'totalTime'  => '8:00',
                'user'       => $staff,
            ];
        }

        $prevDate           = $start->copy()->subMonth()->format('Y-m-d');
        $nextDate           = $start->copy()->addMonth()->format('Y-m-d');
        $currentDateDisplay = $today->format('Y/m');

        return view('admin_attendance_staff', compact(
            'staff', 'attendances', 'prevDate', 'nextDate', 'currentDateDisplay'
        ));
    })->name('admin.attendance.staff');

    // 修正申請一覧（管理者用）
    Route::get('/admin/stamp_correction_request/list', function () {
        $revisionRequests = [
            (object)[
                'id'             => 1,
                'status'         => '承認待ち',
                'targetDatetime' => '2025-04-09 09:00〜18:00',
                'reason'         => '打刻ミス',
                'created_at'     => Carbon::today()->subDay(),
            ],
        ];
        return view('admin_stamp_correction_request_list', compact('revisionRequests'));
    })->name('admin.revision.list');

    // 修正申請承認画面（管理者用）
    Route::get('/admin/stamp_correction_request/approve/{id}', function ($id) {
        $detail = (object)[
            'id'              => $id,
            'name'            => '山田太郎',
            'date'            => Carbon::today()->format('Y-m-d'),
            'workTime'        => '09:00〜18:00',
            'break'           => '1:00',
            'break2'          => '0:30',
            'remarks'         => '特になし',
            'approvalComment' => '',
        ];
        return view('admin_stamp_correction_request_approve', compact('detail'));
    })->name('admin.revision.detail');

    // 修正申請承認実行（ダミー）
    Route::post('/admin/stamp_correction_request/approve/{id}', fn() =>
        redirect('/admin/stamp_correction_request/list')->with('success', '承認しました。')
    )->name('admin.revision.approve');

    // 管理者ログアウト
    Route::get('/admin/logout', fn() => Auth::logout() ?: redirect('/admin/login'))
        ->name('admin.logout');
});
