<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\RevisionRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /** 1) 打刻トップ */
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

    /** 2) 打刻 POST */
    public function clock(Request $request)
    {
        $request->validate([
            'type' => ['required','in:clock_in,clock_out,break_in,break_out'],
        ]);

        $user  = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::firstOrCreate(
            ['user_id'=>$user->id,'date'=>$today],
            ['clock_in'=>null,'clock_out'=>null]
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
                    $attendance->breakRecords()->create(['break_start'=>now()]);
                }
                break;
            case 'break_out':
                $open = $attendance->breakRecords()
                                   ->whereNull('break_end')
                                   ->latest('break_start')
                                   ->first();
                if ($open) {
                    $open->update(['break_end'=>now()]);
                }
                break;
        }

        return back();
    }

    /** 3) 月次勤怠一覧 */
    public function list(Request $request)
    {
        $user  = Auth::user();
        $year  = (int)$request->query('year', now()->year);
        $month = (int)$request->query('month', now()->month);
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
                    ->where('user_id',$user->id)
                    ->whereYear('date',$year)
                    ->whereMonth('date',$month)
                    ->orderBy('date')
                    ->get();

        $attendances = $records->map(function($a){
            $sec = $a->breakRecords->sum(fn($b)=>
                strtotime($b->break_end?:now())
              - strtotime($b->break_start)
            );
            return (object)[
                'id'        => $a->id,
                'date'      => Carbon::parse($a->date),
                'clockIn'   => optional($a->clock_in)->format('H:i')?:'-',
                'clockOut'  => optional($a->clock_out)->format('H:i')?:'-',
                'breakTime' => $sec? gmdate('H:i',$sec):'-',
                'totalTime' => ($a->clock_in&&$a->clock_out)
                             ? gmdate('H:i',
                                   strtotime($a->clock_out)
                                 - strtotime($a->clock_in)
                                 - $sec)
                             : '-',
            ];
        });

        $noRecords = $attendances->isEmpty();

        return view('attendance_list', compact(
            'attendances','prev','next','currentMonth','noRecords'
        ));
    }

    /**
     * 4) 勤怠詳細
     *    - 数値ID or 日付キー (admin: staff_id でダミー or 他ユーザー)
     */
    public function detail($key, Request $request)
    {
        $user     = Auth::user();
        $isAdmin  = $user->is_admin;
        $staffId  = $request->query('staff_id');
        $dummyMap = [
            1=>'山田 太郎',2=>'西 伶奈',3=>'増田 一世',
            4=>'山本 敬吉',5=>'秋田 朋美',6=>'中西 教夫',
        ];

        // — 数値ID の場合はそのまま取得 —
        if (ctype_digit($key)) {
            $attendance = Attendance::with('breakRecords','user')
                ->where('id',$key)->firstOrFail();
        }
        // — 日付キー の場合 —
        elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/',$key)) {
            // (A) 管理者 + ダミー対象 staff_id → ダミー詳細
            if ($isAdmin && $staffId && isset($dummyMap[$staffId])) {
                [$last,$first] = explode(' ',$dummyMap[$staffId],2) + [1=>''];
                $detail = (object)[
                    'id'       => $staffId.'_'.str_replace('-','',$key),
                    'user'     => (object)['last_name'=>$last,'first_name'=>$first,'name'=>$dummyMap[$staffId]],
                    'date'     => $key,
                    'clockIn'  => '09:00',
                    'clockOut' => '18:00',
                    'breaks'   => [['start'=>'12:00','end'=>'13:00']],
                    'remarks'  => '',
                ];
                return view('attendance_detail', compact('detail'));
            }
            // (B) 管理者 + staff_id → 他ユーザーの実データ
            if ($isAdmin && $staffId) {
                $attendance = Attendance::with('breakRecords','user')
                    ->where('user_id',$staffId)
                    ->whereDate('date',$key)
                    ->firstOrFail();
            }
            // (C) 一般ユーザー → 自分の実データ
            else {
                $attendance = Attendance::with('breakRecords','user')
                    ->where('user_id',$user->id)
                    ->whereDate('date',$key)
                    ->firstOrFail();
            }
        }
        else {
            abort(404);
        }

        // — 実レコードから detail 構築 —
        $breaks = $attendance->breakRecords->map(fn($b)=>[
            'start' => Carbon::parse($b->break_start)->format('H:i'),
            'end'   => $b->break_end
                         ? Carbon::parse($b->break_end)->format('H:i')
                         : '',
        ])->all();

        $detail = (object)[
            'id'       => $attendance->id,
            'user'     => $attendance->user,
            'date'     => $attendance->date,
            'clockIn'  => optional($attendance->clock_in)->format('H:i')?:'',
            'clockOut' => optional($attendance->clock_out)->format('H:i')?:'',
            'breaks'   => $breaks,
            'remarks'  => $attendance->remarks ?? '',
        ];

        return view('attendance_detail', compact('detail'));
    }

    /** 5) 修正申請 */
    public function update(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        RevisionRequest::create([
            'user_id'             => Auth::id(),
            'attendance_id'       => $attendance->id,
            'requested_clock_in'  => $request->clock_in,
            'requested_clock_out' => $request->clock_out,
            'breaks'              => json_encode($request->breaks),
            'reason'              => $request->remarks,
            'status'              => 'pending',
        ]);

        return back()->with('pending', true);
    }
}
