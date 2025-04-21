<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests\AttendanceRequest;  // 後で FormRequest を使う場合

class AttendanceController extends Controller
{
    // 打刻画面
    public function index()
    {
        return view('attendance');
    }

    // 勤怠一覧（ダミー表示）
    public function list(Request $request)
    {
        // 年月をクエリから or 今月
        $year  = $request->query('year',  Carbon::now()->format('Y'));
        $month = $request->query('month', Carbon::now()->format('m'));

        $firstOfMonth = Carbon::create($year, $month, 1);
        $today        = Carbon::today();

        // 前月／翌月リンク用
        $prevDt = $firstOfMonth->copy()->subMonth();
        $nextDt = $firstOfMonth->copy()->addMonth();
        $prev = (object)[
            'year'  => $prevDt->format('Y'),
            'month' => $prevDt->format('m'),
        ];
        $next = (object)[
            'year'  => $nextDt->format('Y'),
            'month' => $nextDt->format('m'),
        ];

        // 見た目用「YYYY年MM月」
        $currentMonth = $firstOfMonth->format('Y年m月');

        // ダミーデータ生成
        $attendances = [];
        // 先月１日〜末日
        for ($d = $prevDt->copy()->startOfMonth(); $d->lte($prevDt->copy()->endOfMonth()); $d->addDay()) {
            $attendances[] = (object)[
                'date'      => $d->copy(),
                'clockIn'   => '09:00',
                'clockOut'  => '18:00',
                'breakTime' => '1:00',
                'totalTime' => '8:00',
            ];
        }
        // 今月
        for ($d = $firstOfMonth->copy(); $d->lte($firstOfMonth->copy()->endOfMonth()); $d->addDay()) {
            if ($d->lte($today)) {
                $attendances[] = (object)[
                    'date'      => $d->copy(),
                    'clockIn'   => '09:00',
                    'clockOut'  => '18:00',
                    'breakTime' => '1:00',
                    'totalTime' => '8:00',
                ];
            } else {
                $attendances[] = (object)[
                    'date'      => $d->copy(),
                    'clockIn'   => null,
                    'clockOut'  => null,
                    'breakTime' => null,
                    'totalTime' => null,
                ];
            }
        }

        return view('attendance_list', compact(
            'attendances',
            'prev', 'next', 'currentMonth'
        ));
    }

    // 勤怠詳細（ダミー）
    public function detail($id)
    {
        $detail = (object)[
            'id'       => $id,
            'date'     => Carbon::today()->format('Y-m-d'),
            'clockIn'  => '09:00',
            'clockOut' => '18:00',
            'breaks'   => [['start'=>'12:00','end'=>'12:30']],
            'remarks'  => '特になし',
        ];
        return view('attendance_detail', compact('detail'));
    }

    // 修正申請（常に「承認待ち」へ）
    public function update(AttendanceRequest $request, $id)
    {
        // まだ DB には保存せず、承認待ちフラグだけ返す
        return back()->with('pending', true);
    }
}
