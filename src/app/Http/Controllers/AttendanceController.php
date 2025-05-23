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
        } elseif ($attendance->breakRecords()->whereNull('break_end')->exists()) {
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
        $today = Carbon::today();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            ['clock_in' => null, 'clock_out' => null]
        );

        switch ($request->type) {
            case 'clock_in':
                if (! $attendance->clock_in) {
                    $attendance->clock_out = $time;
                    $attendance->save();
                }
                break;
            case 'clock_out':
                if (! $attendance->clock_out) {
                    $attendance->clock_out = $time;
                    $attendance->save();
                }
                break;
            case 'break_in':
                if (! $attendance->breakRecords()->whereNull('break_end')->exists()) {
                    $attendance->breakRecords()->create(['break_start' => now()]);
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

        $attendances = $records->map(function ($a) {
            $breakSec = $a->breakRecords->sum(function ($b) {
                $end = $b->break_end ?? now();
                return strtotime($end) - strtotime($b->break_start);
            });

            return (object)[
                'id'        => $a->id,
                'date'      => Carbon::parse($a->date),
                'clockIn'   => optional($a->clock_in)->format('H:i') ?? '-',
                'clockOut'  => optional($a->clock_out)->format('H:i') ?? '-',
                'breakTime' => $breakSec ? gmdate('H:i', $breakSec) : '-',
                'totalTime' => ($a->clock_in && $a->clock_out)
                             ? gmdate('H:i', strtotime($a->clock_out) - strtotime($a->clock_in) - $breakSec)
                             : '-',
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
                ->whereDate('date', $date)
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
                'start' => optional($b->break_start)->format('H:i'),
                'end'   => optional($b->break_end)->format('H:i'),
            ])->toArray();

        $hasPending     = false;
        $pendingRequest = null;
        if ($detail->id) {
            $pendingRequest = RevisionRequest::where([
                                    ['attendance_id', $detail->id],
                                    ['user_id', Auth::id()],
                                    ['status', 'pending'],
                                ])->latest('created_at')->first();
            if ($pendingRequest) {
                $hasPending = true;
            }
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
            'user_id'           => Auth::id(),
            'attendance_id'     => $id,
            'original_clock_in' => $attendance->clock_in,
            'original_clock_out'=> $attendance->clock_out,
            'original_remarks'  => $attendance->remarks,
            'proposed_clock_in' => $request->clock_in,
            'proposed_clock_out'=> $request->clock_out,
            'proposed_remarks'  => $request->remarks,
            'status'            => 'pending',
            'breaks'            => $request->breaks,
        ]);

        return back()->with('success', '修正申請を送信しました。管理者の承認をお待ちください。');
    }

    // スタッフ別勤怠一覧
    public function staffAttendance(Request $request, $id)
    {
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
                ->whereDate('date', '<=', $today)
                ->orderBy('date')
                ->get()
                ->map(function ($a) {
                    $sec     = $a->breakRecords->sum(fn ($b) => strtotime($b->break_end ?: now()) - strtotime($b->break_start));
                    $workSec = ($a->clock_in && $a->clock_out)
                             ? strtotime($a->clock_out) - strtotime($a->clock_in) - $sec
                             : 0;
                    return (object)[
                        'id'         => $a->id,
                        'created_at' => Carbon::parse($a->date),
                        'clockIn'    => optional($a->clock_in)->format('H:i') ?: '-',
                        'clockOut'   => optional($a->clock_out)->format('H:i') ?: '-',
                        'breakTime'  => $sec ? gmdate('H:i', $sec) : '-',
                        'totalTime'  => $a->clock_in && $a->clock_out
                                       ? gmdate('H:i', max(0, $workSec))
                                       : '-',
                    ];
                });
        }

        return view('admin_attendance_staff', compact('staff', 'attendances', 'prevDate', 'nextDate'))
               ->with('currentDateDisplay', $currentMonth);
    }

    /**
     * スタッフ別勤怠一覧の CSV エクスポート
     */
    public function exportStaffCsv(Request $request, $id): StreamedResponse
    {
        $user  = User::findOrFail($id);
        $month = $request->query('month', now()->format('Y-m'));
        [$year, $m] = explode('-', $month);

        $records = Attendance::with('breakRecords')
            ->where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $m)
            ->orderBy('date')
            ->get();

        $filename = "attendance_{$user->id}_{$month}.csv";

        return response()->streamDownload(function () use ($records) {
            $out = fopen('php://output', 'w');
            // Excel で文字化けしないよう BOM を出力
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            // ヘッダー行
            fputcsv($out, ['日付', '出勤', '退勤', '休憩合計', '備考']);

            foreach ($records as $att) {
                $sec      = $att->breakRecords->sum(fn($b) => strtotime($b->break_end ?: now()) - strtotime($b->break_start));
                $breakSum = gmdate('H:i', $sec);

                fputcsv($out, [
                    $att->date,
                    optional($att->clock_in)->format('H:i') ?? '',
                    optional($att->clock_out)->format('H:i') ?? '',
                    $breakSum,
                    $att->remarks,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
