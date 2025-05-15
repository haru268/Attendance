<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\BreakRecord;
use App\Models\RevisionRequest;
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
        $today = Carbon::today()->toDateString(); // YYYY-MM-DD

        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', $today)
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
            'type' => ['required','in:clock_in,clock_out,break_in,break_out']
        ]);

        $user  = Auth::user();
        $today = Carbon::today()->toDateString();

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
                    BreakRecord::create([
                        'attendance_id' => $attendance->id,
                        'break_start'   => now(),
                    ]);
                }
                break;

            case 'break_out':
                $open = $attendance->breakRecords()
                                   ->whereNull('break_end')
                                   ->latest('break_start')
                                   ->first();
                if ($open) {
                    $open->update(['break_end' => now()]);
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

        $records = Attendance::with('breakRecords')
                    ->where('user_id', $user->id)
                    ->whereYear('date',  $year)
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
                'clockIn'   => optional($a->clock_in)->format('H:i'),
                'clockOut'  => optional($a->clock_out)->format('H:i'),
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
     * 4) 勤怠詳細表示
     *    日付(YYYY-MM-DD) or 数値ID で取得
     */
    public function detail($key)
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $key)) {
            $attendance = Attendance::with('breakRecords')
                            ->where('user_id', Auth::id())
                            ->whereDate('date', $key)
                            ->firstOrFail();
        } elseif (preg_match('/^[0-9]+$/', $key)) {
            $attendance = Attendance::with('breakRecords')
                            ->findOrFail($key);
        } else {
            abort(404);
        }

        // 自身の承認待ち申請があるか
        $isPending = RevisionRequest::where('attendance_id', $attendance->id)
                                   ->where('user_id', Auth::id())
                                   ->where('status', 'pending')
                                   ->exists();

        return view('attendance_detail', compact('attendance','isPending'));
    }

    /**
     * 5) 修正申請作成 (PATCH)
     */
    public function update(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // テーブルのカラム名に合わせて保存
        RevisionRequest::create([
            'user_id'             => Auth::id(),
            'attendance_id'       => $attendance->id,
            'requested_clock_in'  => $request->input('clock_in'),
            'requested_clock_out' => $request->input('clock_out'),
            'reason'              => $request->input('remarks'),
            // status はマイグレーションの default で 'pending'
        ]);

        // 詳細画面に戻る
        return back();
    }
}
