<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * 1) 打刻トップ
     */
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
        } elseif ($attendance->breakRecords()->whereNull('break_end')->exists()) {
            $status = '休憩中';
        } else {
            $status = '出勤中';
        }

        return view('attendance', compact('status'));
    }

    /**
     * 2) 打刻 POST
     */
    public function clock(Request $request)
    {
        $request->validate([
            'type' => ['required','in:clock_in,clock_out,break_in,break_out'],
        ]);

        $user  = Auth::user();
        $today = Carbon::today();

        // 今日のレコードを取得または作成
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            ['clock_in' => null, 'clock_out' => null]
        );

        switch ($request->type) {
            case 'clock_in':
                if (! $attendance->clock_in) {
                    $attendance->clock_in = now();
                    $attendance->save();
                }
                break;

            case 'clock_out':
                if (! $attendance->clock_out) {
                    $attendance->clock_out = now();
                    $attendance->save();
                }
                break;

            case 'break_in':
                if (! $attendance->breakRecords()->whereNull('break_end')->exists()) {
                    $attendance->breakRecords()->create([
                        'break_start' => now(),
                    ]);
                }
                break;

            case 'break_out':
                $open = $attendance->breakRecords()
                                   ->whereNull('break_end')
                                   ->latest('break_start')
                                   ->first();
                if ($open) {
                    $open->break_end = now();
                    $open->save();
                }
                break;
        }

        return back();
    }

    /**
     * 3) 月次勤怠一覧
     */
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

    // 「登録日以降」のレコードだけ取得
    $records = Attendance::with('breakRecords')
                ->where('user_id', $user->id)
                ->whereDate('date', '>=', $user->created_at->toDateString())
                ->whereYear('date',  $year)
                ->whereMonth('date', $month)
                ->orderBy('date')
                ->get();

    $attendances = $records->map(function ($a) {
        $breakSec = $a->breakRecords->sum(function ($b) {
            $end = $b->break_end ?? now();
            return strtotime($end) - strtotime($b->break_start);
        });

        return (object) [
            'id'        => $a->id,
            'date'      => Carbon::parse($a->date),
            'clockIn'   => optional($a->clock_in)->format('H:i') ?? '-',
            'clockOut'  => optional($a->clock_out)->format('H:i') ?? '-',
            'breakTime' => $breakSec ? gmdate('H:i', $breakSec) : '-',
            'totalTime' => ($a->clock_in && $a->clock_out)
                         ? gmdate('H:i',
                              strtotime($a->clock_out)
                            - strtotime($a->clock_in)
                            - $breakSec)
                         : '-',
        ];
    });

    $noRecords = $attendances->isEmpty();

    return view('attendance_list', compact(
        'attendances', 'prev', 'next', 'currentMonth', 'noRecords'
    ));
}

    /**
     * 4) 詳細表示
     *
     * @param Request $request
     * @param string  $key    数字(ID)または日付文字列(YYYY-MM-DD,YYYYMMDD)
     */
    public function detail(Request $request, $key)
    {
        // 管理者が他ユーザーを見るときの staff_id
        $staffId = $request->query('staff_id');
        $user    = $staffId
                 ? User::findOrFail($staffId)
                 : Auth::user();

        if (ctype_digit($key)) {
            // ID 指定
            $attendance = Attendance::with('breakRecords','user')->findOrFail((int)$key);
            $detail     = $attendance;
        } else {
            // 日付指定
            if (preg_match('/^\d{8}$/', $key)) {
                $date = Carbon::createFromFormat('Ymd', $key);
            } else {
                $date = Carbon::parse($key);
            }

            $attendance = Attendance::with('breakRecords')
                ->where('user_id', $user->id)
                ->whereDate('date', $date)
                ->first();

            if ($attendance) {
                $detail = $attendance;
            } else {
                // レコードなし → ダミー表示
                $detail = (object)[
                    'id'           => null,
                    'user'         => $user,
                    'date'         => $date->toDateString(),
                    'clock_in'     => null,
                    'clock_out'    => null,
                    'remarks'      => '',
                    'breakRecords' => collect(),
                ];
            }
        }

        // breaks 配列を整形
        $detail->breaks = collect($detail->breakRecords ?? [])
            ->map(fn($b) => [
                'start' => optional($b->break_start)->format('H:i'),
                'end'   => optional($b->break_end  )->format('H:i'),
            ])->toArray();

        return view('attendance_detail', compact('detail'));
    }

    /**
     * 5) 修正申請 (更新)
     */
    public function update(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // 時刻・備考を更新
        $attendance->clock_in  = $request->clock_in;
        $attendance->clock_out = $request->clock_out;
        $attendance->remarks   = $request->remarks;
        $attendance->save();

        // 休憩レコードも順に更新
        foreach ($request->breaks as $i => $br) {
            $record = $attendance->breakRecords()
                         ->orderBy('break_start')
                         ->skip($i)
                         ->first();
            if ($record) {
                $record->break_start = $br['start'];
                $record->break_end   = $br['end'];
                $record->save();
            }
        }

        return back()->with('pending', true);
    }

    /**
     * 6) 管理者：スタッフ別月次勤怠一覧
     */
    public function staffAttendance(Request $request, $id)
{
    // ① 対象年月の取得
    $monthTop     = Carbon::parse($request->query('date', now()->startOfMonth()));
    $prevDate     = $monthTop->copy()->subMonth()->format('Y-m-01');
    $nextDate     = $monthTop->copy()->addMonth()->format('Y-m-01');
    $currentMonth = $monthTop->format('Y/m');
    $today        = Carbon::today();

    // ② スタッフ情報を取得
    $staff = User::findOrFail($id);

    // ③ ダミー or 実ユーザー で分岐
    if ($staff->is_dummy) {
        // ダミーユーザー：未来月は何も表示せず、
        // 過去月は月末まで、当月は今日までをダミーデータ生成
        if ($monthTop->gt($today->copy()->startOfMonth())) {
            $attendances = collect();
        } else {
            $start = $monthTop->copy()->startOfMonth();
            $end   = ($monthTop->year === $today->year && $monthTop->month === $today->month)
                   ? $today
                   : $monthTop->copy()->endOfMonth();

            $attendances = collect();
            for ($d = $start; $d->lte($end); $d->addDay()) {
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
        // 実ユーザー：登録日以降かつ当日以前のレコードだけ取得
        $registrationDate = $staff->created_at->toDateString();
        $attendances = Attendance::with('breakRecords')
            ->where('user_id', $id)
            ->whereYear('date',  $monthTop->year)
            ->whereMonth('date', $monthTop->month)
            ->whereDate('date', '>=', $registrationDate)
            ->whereDate('date', '<=', $today->toDateString())
            ->orderBy('date')
            ->get()
            ->map(function ($a) {
                $sec = $a->breakRecords->sum(fn($b) =>
                    strtotime($b->break_end ?: now()) - strtotime($b->break_start)
                );
                $workSec = ($a->clock_in && $a->clock_out)
                          ? strtotime($a->clock_out)
                            - strtotime($a->clock_in)
                            - $sec
                          : 0;
                return (object)[
                    'id'         => $a->id,
                    'created_at' => Carbon::parse($a->date),
                    'clockIn'    => optional($a->clock_in)->format('H:i')  ?: '-',
                    'clockOut'   => optional($a->clock_out)->format('H:i') ?: '-',
                    'breakTime'  => $sec ? gmdate('H:i', $sec) : '-',
                    'totalTime'  => ($a->clock_in && $a->clock_out)
                                  ? gmdate('H:i', max(0, $workSec))
                                  : '-',
                ];
            });
    }

    // ④ ビューに返す
    return view('admin_attendance_staff', compact(
        'staff', 'attendances', 'prevDate', 'nextDate'
    ))->with('currentDateDisplay', $currentMonth);
}
}
