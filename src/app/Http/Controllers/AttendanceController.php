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
 *
 * @param  string  $key      数値 ID または YYYY-MM-DD
 * @param  Request $request
 */
public function detail($key, Request $request)
{
    $user    = Auth::user();
    $isAdmin = $user->is_admin;
    $staffId = $request->query('staff_id');

    // ダミー対象スタッフ一覧
    $dummyMap = [
        1 => '山田 太郎',
        2 => '西 伶奈',
        3 => '増田 一世',
        4 => '山本 敬吉',
        5 => '秋田 朋美',
        6 => '中西 教夫',
    ];

    // ―― 日付キーかつ管理者でダミー指定がある場合は即ダミー表示 ――
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $key)
        && $isAdmin
        && $staffId
        && isset($dummyMap[$staffId])
    ) {
        // 氏名を「姓」「名」に分割
        [$last, $first] = explode(' ', $dummyMap[$staffId], 2) + [1 => ''];
        $detail = (object)[
            'id'        => 'dummy_'.$staffId.'_'.$key,
            'user'      => (object)[
                'last_name'  => $last,
                'first_name' => $first,
                'name'       => $dummyMap[$staffId],
            ],
            'date'      => $key,
            'clockIn'   => '09:00',
            'clockOut'  => '18:00',
            'breaks'    => [
                ['start' => '12:00', 'end' => '13:00'],
            ],
            'remarks'   => '',
        ];

        return view('attendance_detail', compact('detail'));
    }

    // ―― 数値 ID の場合は実データを取得 ――
    if (ctype_digit($key)) {
        $attendance = Attendance::with('breakRecords','user')
            ->findOrFail($key);
    }
    // ―― YYYY-MM-DD の場合は該当ユーザーの実データを取得 ――
    elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $key)) {
        $query = Attendance::with('breakRecords','user')
                   ->whereDate('date', $key);

        // 管理者かつ staff_id があれば他ユーザー
        if ($isAdmin && $staffId) {
            $attendance = $query->where('user_id', $staffId)->firstOrFail();
        }
        // 一般ユーザーは自分の
        else {
            $attendance = $query->where('user_id', $user->id)->firstOrFail();
        }
    }
    else {
        abort(404);
    }

    // ―― 実レコードを detail オブジェクトに整形 ――
    $breaks = $attendance->breakRecords->map(fn($b) => [
        'start' => Carbon::parse($b->break_start)->format('H:i'),
        'end'   => $b->break_end
                     ? Carbon::parse($b->break_end)->format('H:i')
                     : '',
    ])->all();

    $detail = (object)[
        'id'        => $attendance->id,
        'user'      => $attendance->user,
        'date'      => $attendance->date,
        'clockIn'   => optional($attendance->clock_in)->format('H:i') ?: '',
        'clockOut'  => optional($attendance->clock_out)->format('H:i') ?: '',
        'breaks'    => $breaks,
        'remarks'   => $attendance->remarks ?? '',
    ];

    return view('attendance_detail', compact('detail'));
}

    /**
     * 5) 修正申請 / 更新
     *    管理者は即時更新、一般は申請作成
     */
    public function update(AttendanceRequest $request, $id)
    {
        $user = Auth::user();

        // —— 管理者なら即時更新 —— 
        if ($user->is_admin) {
            $attendance = Attendance::with('breakRecords')->findOrFail($id);

            // メインの時刻・備考を更新
            $attendance->update([
                'clock_in'  => $request->clock_in,
                'clock_out' => $request->clock_out,
                'remarks'   => $request->remarks,
            ]);

            // 休憩レコードを上書き
            $breaksInput = $request->breaks;
            foreach ($attendance->breakRecords as $index => $break) {
                if (isset($breaksInput[$index])) {
                    $break->update([
                        'break_start' => $breaksInput[$index]['start'],
                        'break_end'   => $breaksInput[$index]['end'],
                    ]);
                }
            }

            return back()->with('success','修正を保存しました。');
        }

        // —— 一般ユーザーは申請を作成 —— 
        RevisionRequest::create([
            'user_id'             => $user->id,
            'attendance_id'       => $id,
            'requested_clock_in'  => $request->clock_in,
            'requested_clock_out' => $request->clock_out,
            'breaks'              => json_encode($request->breaks),
            'reason'              => $request->remarks,
            'status'              => 'pending',
        ]);

        return back()->with('pending', true);
    }
}
