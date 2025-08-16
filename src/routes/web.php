<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\AttendanceController;
use App\Models\Attendance;
use App\Models\RevisionRequest;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\AttendanceRequest;
use App\Http\Requests\AdminLoginRequest;

/* =========================================================
|  トップページ（疎通確認用）
========================================================= */
Route::get('/', function () {
    return response('OK', 200);
});

/* =========================================================
|  一般ユーザー：登録 & ログイン
========================================================= */
Route::get('/register', fn () => view('register'))->name('register.form');

Route::post('/register', function (RegisterRequest $r) {
    $user = User::create([
        'name'     => $r->name,
        'email'    => $r->email,
        'password' => bcrypt($r->password),
    ]);
    Auth::login($user);
    return redirect()->route('attendance')->with('success', '登録が完了しました。');
})->name('register');

Route::get('/login', fn () => view('login'))->name('login.form');

Route::post('/login', function (LoginRequest $r) {
    if (Auth::attempt($r->only('email', 'password'))) {
        // 管理者アカウントは一般ログイン不可
        if (Auth::user()->is_admin) {
            Auth::logout();
            return back()->withErrors(['email' => '一般ユーザー用のアカウントでログインしてください。'])->withInput();
        }
        return redirect()->route('attendance');
    }
    return back()->withErrors(['email' => 'ログイン情報が登録されていません'])->withInput();
})->name('login');

/* =========================================================
|  一般ユーザー：保護ルート
========================================================= */
Route::middleware('auth')->group(function () {

    /* ---------- 勤怠関連 ---------- */
    Route::get('/attendance',               [AttendanceController::class, 'index'])->name('attendance');
    Route::post('/attendance/clock',        [AttendanceController::class, 'clock'])->name('attendance.clock');
    Route::get('/attendance/list',          [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/{key}',         [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::patch('/attendance/update/{id}', [AttendanceController::class, 'update'])->name('attendance.update');

    /* ---------- 申請一覧（一般 / 管理者 共通の実体はここ） ---------- */
    Route::get('/stamp_correction_request/list', function (Request $req) {
        $status = $req->query('status', 'pending');          // pending / approved
        $isAdm  = Auth::user()->is_admin;                    // true → 全件、false → 自分のみ

        $query = RevisionRequest::with(['user', 'attendance'])
                   ->where('status', $status)
                   ->orderByDesc('created_at');

        if (! $isAdm) {                                      // 一般ユーザー
            $query->where('user_id', Auth::id());
        }
        $revisionRequests = $query->get();

        return view('stamp_correction_request_list', [
            'revisionRequests' => $revisionRequests,
            'status'           => $status,
            'isAdmin'          => $isAdm,
        ]);
    })->name('stamp_correction_request.list');

    /* ---------- ログアウト ---------- */
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});

/* =========================================================
|  管理者：ログイン
========================================================= */
Route::get('/admin/login', fn () => view('admin_login'))->name('admin.login.form');

Route::post('/admin/login', function (AdminLoginRequest $r) {
    if (Auth::attempt($r->only('email', 'password'))) {
        if (! Auth::user()->is_admin) {
            Auth::logout();
            return back()->withErrors(['email' => '管理者アカウントのみログインできます。'])->withInput();
        }
        return redirect()->route('admin.attendance.list');
    }
    return back()->withErrors(['email' => 'ログイン情報が登録されていません'])->withInput();
})->name('admin.login');

/* =========================================================
|  管理者：保護ルート
========================================================= */
Route::prefix('admin')->middleware(['auth', 'can:admin-only'])->group(function () {

    /* ---------- 日次勤怠一覧 ---------- */
    Route::get('/attendance/list', function (Request $r) {
        $sel      = Carbon::parse($r->query('date', now()->toDateString()));
        $today    = Carbon::today();
        $prevDate = $sel->copy()->subDay()->toDateString();
        $nextDate = $sel->copy()->addDay()->toDateString();

        $attendances = collect();
        if (! $sel->gt($today)) {
            $users = User::where('is_admin', false)->get();
            foreach ($users as $u) {
                if ($u->is_dummy) {
                    $attendances->push((object)[
                        'id'         => null,
                        'user'       => $u,
                        'created_at' => $sel,
                        'clockIn'    => '09:00',
                        'clockOut'   => '18:00',
                        'breakTime'  => '1:00',
                        'totalTime'  => '08:00',
                    ]);
                    continue;
                }
                if ($u->created_at->toDateString() > $sel->toDateString()) {
                    continue;
                }

                $att = Attendance::with('breakRecords')
                        ->where('user_id', $u->id)
                        ->whereDate('date', $sel)
                        ->first();
                if (! $att) {
                    continue;
                }

                $sec  = $att->breakRecords->sum(fn ($b) =>
                    strtotime($b->break_end ?: now()) - strtotime($b->break_start)
                );
                $work = ($att->clock_in && $att->clock_out)
                      ? strtotime($att->clock_out) - strtotime($att->clock_in) - $sec
                      : 0;

                $attendances->push((object)[
                    'id'         => $att->id,
                    'user'       => $u,
                    'created_at' => Carbon::parse($att->date),
                    'clockIn'    => optional($att->clock_in)->format('H:i') ?: '-',
                    'clockOut'   => optional($att->clock_out)->format('H:i') ?: '-',
                    'breakTime'  => $sec ? gmdate('H:i', $sec) : '-',
                    'totalTime'  => $work ? gmdate('H:i', $work) : '-',
                ]);
            }
        }

        return view('admin_attendance_list', compact('attendances', 'prevDate', 'nextDate'))
               ->with('currentDateDisplay', $sel->format('Y/m/d'));
    })->name('admin.attendance.list');

    /* ---------- 勤怠詳細（共通 Controller 処理） ---------- */
    Route::get('/attendance/detail/{key}', [AttendanceController::class, 'detail'])
         ->where('key', '[0-9]{4}-[0-9]{2}-[0-9]{2}|[0-9]+')
         ->name('admin.attendance.detail');

    /* ---------- スタッフ別月次勤怠 ---------- */
    Route::get('/attendance/staff/{id}', [AttendanceController::class, 'staffAttendance'])
         ->name('admin.attendance.staff');

    /* ---------- 修正申請承認 ---------- */
    Route::post('/stamp_correction_request/approve/{id}', function ($id) {
        $rev = RevisionRequest::where('id', $id)->where('status', 'pending')->firstOrFail();
        $att = $rev->attendance;

        // Attendance に反映
        $att->clock_in  = $rev->proposed_clock_in;
        $att->clock_out = $rev->proposed_clock_out;
        $att->remarks   = $rev->proposed_remarks;
        $att->save();

        // 休憩更新
        $att->breakRecords()->delete();
        // $rev->breaks は JSON 文字列想定
        $breaks = json_decode($rev->breaks, true) ?: [];
        foreach ($breaks as $bk) {
            $att->breakRecords()->create([
                'break_start' => $bk['start'] ?: null,
                'break_end'   => $bk['end']   ?: null,
            ]);
        }

        // ステータス変更
        $rev->status = 'approved';
        $rev->save();

        return back()->with('success', '承認しました。');
    })->name('admin.revision.approve');

    /* ---------- スタッフ一覧 ---------- */
    Route::get('/staff/list', function () {
        $staff = User::where('is_admin', false)->get(['id', 'name', 'email']);
        return view('admin_staff_list', compact('staff'));
    })->name('admin.staff.list');

    /* ---------- 管理者：勤怠の直接修正（既存レコード更新） ---------- */
    Route::post('/attendance/update/{id}', [AttendanceController::class, 'adminUpdate'])
        ->name('admin.attendance.update');

    /* ---------- 管理者：勤怠の直接修正（レコードが無い日の upsert） ---------- */
    Route::post('/attendance/update', [AttendanceController::class, 'adminUpsert'])
        ->name('admin.attendance.upsert');

    /* ---------- 管理者：スタッフ勤怠 CSV エクスポート ---------- */
    Route::get('/attendance/staff/{id}/export', [AttendanceController::class, 'exportStaffCsv'])
        ->name('admin.attendance.staff.export');

    /* ---------- 管理者ログアウト ---------- */
    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/admin/login');
    })->name('admin.logout');
});
