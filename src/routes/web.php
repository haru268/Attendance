<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\AttendanceController;

/* ───── FormRequest クラス ───── */
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\AttendanceRequest;
use App\Http\Requests\RevisionRequest;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\AdminAttendanceDetailRequest;
use App\Http\Requests\AdminRevisionRequest;

/* ========================================================================
   ■ 一般ユーザー向けルート
   ===================================================================== */

/* 会員登録フォーム */
Route::get('/register', fn() => view('register'))
    ->name('register.form');

/* 会員登録処理：ユーザー作成→自動ログイン→打刻画面へ */
Route::post('/register', function (RegisterRequest $request) {
    // 新規ユーザー作成
    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => bcrypt($request->password),
    ]);

    // ログイン
    Auth::login($user);

    // 打刻トップへ
    return redirect()->route('attendance')
                     ->with('success', '登録が完了しました。');
})->name('register');

/* ログインフォーム */
Route::get('/login', fn() => view('login'))
    ->name('login.form');

/* ログイン処理 */
Route::post('/login', function (LoginRequest $request) {
    $credentials = $request->only('email','password');
    if (Auth::attempt($credentials)) {
        // 管理者は一般画面へ入れない
        if (Auth::user()->is_admin) {
            Auth::logout();
            return back()
                ->withErrors(['email' => '一般ユーザー用のアカウントでログインしてください。'])
                ->withInput();
        }
        return redirect()->route('attendance');
    }
    return back()
        ->withErrors(['email' => '認証に失敗しました。'])
        ->withInput();
})->name('login');

/* 認証必須エリア（一般ユーザー） */
Route::middleware('auth')->group(function () {
    /* 打刻トップ */
    Route::get('/attendance', [AttendanceController::class,'index'])
         ->name('attendance');

    /* 月次勤怠一覧（ダミー） */
    Route::get('/attendance/list', function (Request $request) {
        // 年・月パラメータ（デフォルト：今月）
        $year  = $request->query('year',  Carbon::now()->year);
        $month = $request->query('month', Carbon::now()->month);

        // 月初と今日
        $first = Carbon::create($year, $month, 1);
        $today = Carbon::today();

        // 前月・翌月リンク用
        $prev = (object)[
            'year'  => $first->copy()->subMonth()->year,
            'month' => $first->copy()->subMonth()->month,
        ];
        $next = (object)[
            'year'  => $first->copy()->addMonth()->year,
            'month' => $first->copy()->addMonth()->month,
        ];

        // 表示用「YYYY/MM」
        $currentMonth = $first->format('Y/m');

        // 未来月なら空、それ以外は当月は今日まで、過去月は月末まで
        if ($first->gt($today->copy()->startOfMonth())) {
            $attendances = [];
        } else {
            $end = $first->isSameMonth($today)
                 ? $today
                 : $first->copy()->endOfMonth();

            $attendances = [];
            for ($d = $first->copy(); $d->lte($end); $d->addDay()) {
                $attendances[] = (object)[
                    'id'         => $d->format('Ymd'),
                    'date'       => $d->copy(),
                    'clockIn'    => '09:00',
                    'clockOut'   => '18:00',
                    'breakTime'  => '1:00',
                    'totalTime'  => '8:00',
                ];
            }
        }

        // データの有無フラグ
        $noRecords = empty($attendances);

        return view('attendance_list', compact(
            'attendances',
            'prev',
            'next',
            'currentMonth',
            'noRecords'
        ));
    })->name('attendance.list');

    /* 勤怠詳細（ダミー） */
    Route::get('/attendance/{id}', [AttendanceController::class,'detail'])
         ->name('attendance.detail');

    /* 勤怠修正申請（PATCH） */
    Route::patch('/attendance/update/{id}', function (AttendanceRequest $request, $id) {
        return back()->with('success','更新しました（ダミー保存）');
    })->name('attendance.update');

    /* 修正申請一覧（一般ユーザー・ダミー） */
    Route::get('/stamp_correction_request/list', function () {
        $revisionRequests = collect(range(1,5))->map(function ($i) {
            $d = Carbon::today()->subDays($i);
            return (object)[
                'id'             => $d->format('Ymd'),
                'status'         => '承認待ち',
                'targetDatetime' => $d->format('Y-m-d').' 09:00〜18:00',
                'reason'         => '打刻ミス',
                'created_at'     => $d,
            ];
        });

        return view('stamp_correction_request_list', compact('revisionRequests'));
    })->name('stamp_correction_request.list');

    /* ログアウト */
    Route::get('/logout', fn() => Auth::logout() ?: redirect('/login'))
         ->name('logout');
});

/* ========================================================================
   ■ 管理者向けルート
   ===================================================================== */

/* 管理者ログインフォーム */
Route::get('/admin/login', fn() => view('admin_login'))
     ->name('admin.login.form');

/* 管理者ログイン処理 */
Route::post('/admin/login', function (AdminLoginRequest $request) {
    if (Auth::attempt($request->only('email','password'))) {
        if (! Auth::user()->is_admin) {
            Auth::logout();
            return back()
                ->withErrors(['email'=>'管理者アカウントのみログインできます。'])
                ->withInput();
        }
        return redirect()->route('admin.attendance.list');
    }
    return back()
        ->withErrors(['email'=>'ログイン情報が登録されていません'])
        ->withInput();
})->name('admin.login');

/* 認証必須エリア（管理者） */
Route::prefix('admin')->middleware('auth')->group(function () {

    /* ─ 日次勤怠一覧 ─ */
    Route::get('/attendance/list', function () {
        $sel      = Carbon::parse(request('date', now()->toDateString()));
        $today    = Carbon::today();
        $prevDate = $sel->copy()->subDay()->toDateString();
        $nextDate = $sel->copy()->addDay()->toDateString();

        if ($sel->gt($today)) {
            // 未来日 → 空リスト
            $attendances = [];
        } else {
            $attendances = collect([
                '山田　太郎','西　伶奈','増田　一世',
                '山本　敬吉','秋田　朋美','中西　教夫',
            ])->map(function ($name) use ($sel) {
                return (object)[
                    'id'         => $sel->format('Ymd').$name,
                    'created_at' => $sel,
                    'clockIn'    => '09:00',
                    'clockOut'   => '18:00',
                    'breakTime'  => '1:00',
                    'totalTime'  => '8:00',
                    'user'       => (object)['name'=>$name],
                ];
            })->all();
        }

        $currentDateDisplay = $sel->format('Y/m/d');
        return view('admin_attendance_list', compact(
            'attendances','prevDate','nextDate','currentDateDisplay'
        ));
    })->name('admin.attendance.list');

    /* ─ 勤怠詳細（管理者）─ */
    Route::get('/attendance/detail/{id}', function ($id) {
        $detail = (object)[
            'id'       => $id,
            'date'     => Carbon::today()->toDateString(),
            'clockIn'  => '09:00',
            'clockOut' => '18:00',
            'breaks'   => [['start'=>'12:00','end'=>'12:30']],
            'remarks'  => '特になし',
            'user'     => (object)['name'=>'山田　太郎'],
        ];
        return view('admin_attendance_detail', compact('detail'));
    })->name('admin.attendance.detail');

    /*  勤怠詳細更新（ダミー） */
    Route::patch('/attendance/detail/{id}', fn(AdminAttendanceDetailRequest $request, $id) =>
        back()->with('success','保存！（ダミー）')
    )->name('admin.attendance.detail.update');

    /* ─ スタッフ一覧 ─ */
    Route::get('/staff/list', function () {
        $staff = collect([
            ['id'=>1,'name'=>'山田　太郎','email'=>'yamada@example.com'],
            ['id'=>2,'name'=>'西　伶奈','email'=>'reina@example.com'],
            ['id'=>3,'name'=>'増田　一世','email'=>'masuda@example.com'],
            ['id'=>4,'name'=>'山本　敬吉','email'=>'yamamoto@example.com'],
            ['id'=>5,'name'=>'秋田　朋美','email'=>'akita@example.com'],
            ['id'=>6,'name'=>'中西　教夫','email'=>'nakanishi@example.com'],
        ])->map(fn($a)=>(object)$a);

        return view('admin_staff_list', compact('staff'));
    })->name('admin.staff.list');

    /* ─ スタッフ別月次勤怠一覧 ─ */
    Route::get('/attendance/staff/{id}', function ($id) {
        $monthTop = Carbon::parse(request('date', now()->startOfMonth()));
        $prevDate = $monthTop->copy()->subMonth()->format('Y-m-01');
        $nextDate = $monthTop->copy()->addMonth()->format('Y-m-01');

        $staff = (object)[
            'id'   => $id,
            'name' => ['','山田　太郎','西　伶奈','増田　一世','山本　敬吉','秋田　朋美','中西　教夫'][$id] ?? '不明',
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

        $currentDateDisplay = $monthTop->format('Y/m');
        return view('admin_attendance_staff', compact(
            'staff','attendances','prevDate','nextDate','currentDateDisplay'
        ));
    })->name('admin.attendance.staff');

    /* ─ 修正申請一覧（管理者用） ─ */
    Route::get('/stamp_correction_request/list', function () {
        $status = request('status', 'pending'); // pending or approved
        $today  = Carbon::today();

        $make = function(string $state, int $i) use ($today) {
            $d = $today->copy()->subDays($i);
            return (object)[
                'id'             => $d->format('Ymd'),
                'status'         => $state,
                'targetDatetime' => $d->format('Y-m-d').' 09:00〜18:00',
                'reason'         => $state === '承認待ち' ? '打刻ミス' : '外出',
                'created_at'     => $d,
                'user'           => (object)['name'=>'山田　太郎'],
            ];
        };

        $all = array_merge(
            array_map(fn($i) => $make('承認待ち', $i), range(1,5)),
            array_map(fn($i) => $make('承認済み', $i), range(6,10))
        );

        $revisionRequests = array_filter($all, fn($r) =>
            $r->status === ($status === 'approved' ? '承認済み' : '承認待ち')
        );

        return view('admin_stamp_correction_request_list', [
            'revisionRequests' => $revisionRequests,
            'status'           => $status,
        ]);
    })->name('admin.revision.list');

    /* ─ 修正申請承認実行（ダミー） ─ */
    Route::post('/stamp_correction_request/approve/{id}', fn(AdminRevisionRequest $request, $id) =>
        redirect()->route('admin.revision.list')->with('success','承認しました。')
    )->name('admin.revision.approve');

    /* 管理者ログアウト */
    Route::get('/logout', fn() => Auth::logout() ?: redirect('/admin/login'))
         ->name('admin.logout');
});
