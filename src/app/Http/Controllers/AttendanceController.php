<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\RevisionRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    // 打刻画面表示
    public function index()
    {
        $user  = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (! $attendance) {
            $status = '勤務外';
        } elseif ($attendance->clock_out) {
            $status = '退勤済';
        } elseif ($attendance && $attendance->breakRecords()->whereNull('break_end')->exists()) {
            $status = '休憩中';
        } else {
            $status = '出勤中';
        }

        return view('attendance', compact('status'));
    }

    // 打刻アクション
    public function clock(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:clock_in,clock_out,break_in,break_out'],
        ]);

        $user  = Auth::user();
        $today = Carbon::today()->toDateString(); // FIX: 日付は文字列(Y-m-d)で統一

        /** @var Attendance $attendance */
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            ['clock_in' => null, 'clock_out' => null]
        );

        $now = Carbon::now();

        switch ($request->type) {
            case 'clock_in':
                if (! $attendance->clock_in) {
                    $attendance->clock_in = $now; // FIX: 正しいフィールドに保存
                    $attendance->save();
                }
                break;

            case 'clock_out':
                if (! $attendance->clock_out) {
                    // FIX: 未終了の休憩があれば自動で閉じる（よくある運用要望）
                    $open = $attendance->breakRecords()->whereNull('break_end')->latest('break_start')->first();
                    if ($open) {
                        $open->break_end = $now;
                        $open->save();
                    }
                    $attendance->clock_out = $now;
                    $attendance->save();
                }
                return back()->with('success', 'お疲れ様でした。');

            case 'break_in':
                // FIX: 退勤済は休憩開始不可
                if ($attendance->clock_out) {
                    return back()->with('error', '退勤後は休憩できません。');
                }
                if (! $attendance->breakRecords()->whereNull('break_end')->exists()) {
                    $attendance->breakRecords()->create(['break_start' => $now]);
                }
                break;

            case 'break_out':
                $open = $attendance->breakRecords()
                    ->whereNull('break_end')
                    ->latest('break_start')
                    ->first();
                if ($open) {
                    $open->break_end = $now;
                    $open->save();
                }
                break;
        }

        return back();
    }

    // 一般ユーザー用勤怠一覧
    public function list(Request $request)
    {
        $user  = Auth::user();
        $year  = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $first = Carbon::create($year, $month, 1);

        $prev = (object)[
            'year'  => $first->copy()->subMonth()->year,
            'month' => $first->copy()->subMonth()->month,
        ];
        $next = (object)[
            'year'  => $first->copy()->addMonth()->year,
            'month' => $first->copy()->addMonth()->month,
        ];
        $currentMonth = $first->format('Y/m');

        $records = Attendance::with('breakRecords')
            ->where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        $attendances = $records->map(function (Attendance $a) {
            // FIX: Carbonで厳密に秒計算
            $breakSec = $a->breakRecords->sum(function ($b) {
                $start = $b->break_start ? Carbon::parse($b->break_start) : null;
                $end   = $b->break_end ? Carbon::parse($b->break_end) : Carbon::now();
                return ($start && $end) ? $start->diffInSeconds($end) : 0;
            });

            $clockIn  = $a->clock_in  ? Carbon::parse($a->clock_in)  : null;
            $clockOut = $a->clock_out ? Carbon::parse($a->clock_out) : null;

            $totalSec = ($clockIn && $clockOut)
                ? max(0, $clockIn->diffInSeconds($clockOut) - $breakSec)
                : null;

            return (object)[
                'id'        => $a->id,
                'date'      => Carbon::parse($a->date),
                'clockIn'   => $clockIn  ? $clockIn->format('H:i')  : '-',
                'clockOut'  => $clockOut ? $clockOut->format('H:i') : '-',
                'breakTime' => $breakSec ? gmdate('H:i', $breakSec) : '-',
                'totalTime' => $totalSec !== null ? gmdate('H:i', $totalSec) : '-',
            ];
        });

        return view('attendance_list', compact('attendances', 'prev', 'next', 'currentMonth'))
            ->with('noRecords', $attendances->isEmpty());
    }

    // 勤怠詳細・修正申請画面
    public function detail(Request $request, $key)
    {
        $staffId = $request->query('staff_id');
        $user    = $staffId ? User::findOrFail($staffId) : Auth::user();

        if (ctype_digit($key)) {
            $attendance = Attendance::with('breakRecords', 'user')->findOrFail((int) $key);
            $detail     = $attendance;
        } else {
            $date = preg_match('/^\d{8}$/', $key)
                ? Carbon::createFromFormat('Ymd', $key)
                : Carbon::parse($key);

            $attendance = Attendance::with('breakRecords')
                ->where('user_id', $user->id)
                ->whereDate('date', $date->toDateString()) // FIX: whereDateに合わせる
                ->first();

            $detail = $attendance ?? (object) [
                'id'           => null,
                'user'         => $user,
                'date'         => $date->toDateString(),
                'clock_in'     => null,
                'clock_out'    => null,
                'remarks'      => '',
                'breakRecords' => collect(),
            ];
        }

        $detail->breaks = collect($detail->breakRecords ?? [])
            ->map(fn ($b) => [
                'start' => $b->break_start ? Carbon::parse($b->break_start)->format('H:i') : null,
                'end'   => $b->break_end   ? Carbon::parse($b->break_end)->format('H:i')   : null,
            ])->toArray();

        $hasPending     = false;
        $pendingRequest = null;

        if (!empty($detail->id)) {
            $pendingRequest = RevisionRequest::where([
   ['attendance_id', $detail->id],
     ['user_id', $user->id],   // ← この勤怠の持ち主
    ['status', 'pending'],
 ])->latest('created_at')->first();

            $hasPending = (bool) $pendingRequest;
        }

        return view('attendance_detail', compact('detail', 'hasPending', 'pendingRequest'));
    }

    // 修正申請送信
    public function update(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        if (RevisionRequest::where([
            ['user_id', Auth::id()],
            ['attendance_id', $id],
            ['status', 'pending'],
        ])->exists()) {
            return back()->with('error', '承認待ちのため修正はできません。');
        }

        RevisionRequest::create([
            'user_id'            => Auth::id(),
            'attendance_id'      => $id,
            'original_clock_in'  => $attendance->clock_in,
            'original_clock_out' => $attendance->clock_out,
            'original_remarks'   => $attendance->remarks,
            'proposed_clock_in'  => $request->clock_in,
            'proposed_clock_out' => $request->clock_out,
            'proposed_remarks'   => $request->remarks,
            'status'             => 'pending',
            'breaks'             => $request->breaks, // JSON想定ならcastsでarray/json
        ]);

        return back()->with('success', '修正申請を送信しました。管理者の承認をお待ちください。');
    }

    // スタッフ別勤怠一覧（管理）
    public function staffAttendance(Request $request, $id)
    {
        // FIX: 管理者限定（簡易ガード）
        if (! (Auth::check() && Auth::user()->is_admin)) {
            abort(403);
        }

        $monthTop     = Carbon::parse($request->query('date', now()->startOfMonth()));
        $prevDate     = $monthTop->copy()->subMonth()->format('Y-m-01');
        $nextDate     = $monthTop->copy()->addMonth()->format('Y-m-01');
        $currentMonth = $monthTop->format('Y/m');
        $today        = Carbon::today();

        $staff = User::findOrFail($id);

        if ($staff->is_dummy) {
            $attendances = collect();

            if (! $monthTop->gt($today->copy()->startOfMonth())) {
                $end = $monthTop->isSameMonth($today) ? $today : $monthTop->copy()->endOfMonth();

                for ($d = $monthTop->copy()->startOfMonth(); $d->lte($end); $d->addDay()) {
                    $attendances->push((object)[
                        'id'         => null,
                        'created_at' => $d->copy(),
                        'clockIn'    => '09:00',
                        'clockOut'   => '18:00',
                        'breakTime'  => '1:00',
                        'totalTime'  => '08:00',
                    ]);
                }
            }
        } else {
            $attendances = Attendance::with('breakRecords')
                ->where('user_id', $id)
                ->whereYear('date', $monthTop->year)
                ->whereMonth('date', $monthTop->month)
                ->whereDate('date', '<=', $today->toDateString()) // FIX: 今日超の将来日を除外
                ->orderBy('date')
                ->get()
                ->map(function (Attendance $a) {
                    $sec = $a->breakRecords->sum(function ($b) {
                        $s = $b->break_start ? Carbon::parse($b->break_start) : null;
                        $e = $b->break_end ? Carbon::parse($b->break_end) : Carbon::now();
                        return ($s && $e) ? $s->diffInSeconds($e) : 0;
                    });

                    $cin  = $a->clock_in  ? Carbon::parse($a->clock_in)  : null;
                    $cout = $a->clock_out ? Carbon::parse($a->clock_out) : null;
                    $work = ($cin && $cout) ? max(0, $cin->diffInSeconds($cout) - $sec) : null;

                    return (object)[
                        'id'         => $a->id,
                        'created_at' => Carbon::parse($a->date),
                        'clockIn'    => $cin  ? $cin->format('H:i')  : '-',
                        'clockOut'   => $cout ? $cout->format('H:i') : '-',
                        'breakTime'  => $sec ? gmdate('H:i', $sec) : '-',
                        'totalTime'  => $work !== null ? gmdate('H:i', $work) : '-',
                    ];
                });
        }

        return view('admin_attendance_staff', compact('staff', 'attendances', 'prevDate', 'nextDate'))
            ->with('currentDateDisplay', $currentMonth);
    }

    /**
     * スタッフ別勤怠一覧の CSV エクスポート（管理）
     */
    public function exportStaffCsv(Request $request, $id): StreamedResponse
    {
        // FIX: 管理者限定
        if (! (Auth::check() && Auth::user()->is_admin)) {
            abort(403);
        }

        $user  = User::findOrFail($id);
        $month = $request->query('month', now()->format('Y-m'));
        [$year, $m] = explode('-', $month);

        $records = Attendance::with('breakRecords')
            ->where('user_id', $user->id)
            ->whereYear('date', (int)$year)
            ->whereMonth('date', (int)$m)
            ->orderBy('date')
            ->get();

        $filename = "attendance_{$user->id}_{$month}.csv";

        return response()->streamDownload(function () use ($records) {
            $out = fopen('php://output', 'w');

            // Excel で文字化けしないよう BOM を出力
            fwrite($out, "\xEF\xBB\xBF"); // FIX: 明示的に書き込み

            // ヘッダー行
            fputcsv($out, ['日付', '出勤', '退勤', '休憩合計', '備考']);

            foreach ($records as $att) {
                // 休憩合計（秒）
                $sec = $att->breakRecords->sum(function ($b) {
                    $s = $b->break_start ? Carbon::parse($b->break_start) : null;
                    $e = $b->break_end ? Carbon::parse($b->break_end) : Carbon::now();
                    return ($s && $e) ? $s->diffInSeconds($e) : 0;
                });

                $breakSum = $sec ? gmdate('H:i', $sec) : '00:00';

                $clockIn  = $att->clock_in  ? Carbon::parse($att->clock_in)->format('H:i')  : '';
                $clockOut = $att->clock_out ? Carbon::parse($att->clock_out)->format('H:i') : '';

                fputcsv($out, [
                    Carbon::parse($att->date)->toDateString(),
                    $clockIn,
                    $clockOut,
                    $breakSum,
                    (string)($att->remarks ?? ''),
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function adminUpdate(Request $request, $id)
{
    if (! (Auth::check() && Auth::user()->is_admin)) abort(403);
    $attendance = Attendance::with('breakRecords')->findOrFail($id);

    // バリデーション（要件票FN039の文言に合わせる）
    $request->validate([
        'clock_in'  => ['nullable','date_format:H:i'],
        'clock_out' => ['nullable','date_format:H:i'],
        'remarks'   => ['required','string','max:255'],
        'breaks'    => ['nullable','array'],
        'breaks.*.start' => ['nullable','date_format:H:i'],
        'breaks.*.end'   => ['nullable','date_format:H:i'],
    ], [
        'clock_in.date_format' => '出勤時間もしくは退勤時間が不適切な値です。',
        'clock_out.date_format' => '出勤時間もしくは退勤時間が不適切な値です。',
        'breaks.*.start.date_format' => '出勤時間もしくは退勤時間が不適切な値です。',
        'breaks.*.end.date_format' => '出勤時間もしくは退勤時間が不適切な値です。',
        'remarks.required' => '備考を記入してください。',
    ]);

    // 勤務時間外チェック
    $ci = $request->clock_in;
    $co = $request->clock_out;
    if ($ci && $co && $ci >= $co) {
        return back()->withErrors(['clock_in' => '出勤時間もしくは退勤時間が不適切な値です。'])->withInput();
    }

// 休憩と勤務時間の相関チェック（要件の指定メッセージ）
  foreach (($request->breaks ?? []) as $i => $b) {
      $bs = $b['start'] ?? null;
      $be = $b['end'] ?? null;
      if ($bs && $be && $bs >= $be) {
          return back()->withErrors(["breaks.$i.start" => '出勤時間もしくは退勤時間が不適切な値です。'])->withInput();
     }
      if ($ci && $bs && $bs < $ci) {
          return back()->withErrors(["breaks.$i.start" => '休憩時間が勤務時間外です。'])->withInput();
      }
      if ($co && $be && $be > $co) {
          return back()->withErrors(["breaks.$i.end" => '休憩時間が勤務時間外です。'])->withInput();
      }
  }

    // 日付を合わせて日時型に
    $date = \Carbon\Carbon::parse($attendance->date)->toDateString();
    $toDateTime = fn($hm) => $hm ? \Carbon\Carbon::parse("$date $hm") : null;

    // 本体更新
    $attendance->clock_in  = $toDateTime($ci);
    $attendance->clock_out = $toDateTime($co);
    $attendance->remarks   = $request->remarks;

    // 休憩の再登録
    $attendance->breakRecords()->delete();
    foreach ($request->breaks ?? [] as $b) {
        $bs = $b['start'] ?? null;
        $be = $b['end'] ?? null;
        if ($bs || $be) {
            $attendance->breakRecords()->create([
                'break_start' => $toDateTime($bs),
                'break_end'   => $toDateTime($be),
            ]);
        }
    }

    $attendance->save();

    return back()->with('success', '修正しました。');
}

}
