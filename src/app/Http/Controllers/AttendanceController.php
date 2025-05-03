<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /* 打刻トップ ------------------------------------------------------- */
    public function index()
    {
        return view('attendance');
    }

    /* 勤怠詳細 --------------------------------------------------------- */
    public function detail($id)
    {
        $dateObj = Carbon::parse($id);   // 例: 20250502

        /* ★ ここを “常に 1 行” に固定 -------------- */
        $breaks = [
            ['start' => '12:00', 'end' => '13:00'],   // 休憩1 行だけ
        ];

        $detail = (object)[
            'id'       => $id,
            'date'     => $dateObj->toDateString(),
            'clockIn'  => '09:00',
            'clockOut' => '18:00',
            'breaks'   => $breaks,                    // ← 1 行渡す
            'remarks'  => '特になし',
            'user'     => (object)[
                'last_name'  => '山田',
                'first_name' => '太郎',
            ],
        ];

        /* 承認待ちかどうかの判定は view 側で session を見るだけ。
           ここでは break 行を増減させない */
        return view('attendance_detail', compact('detail'));
    }

    /* 更新 ------------------------------------------------------------- */
    public function update(Request $request, $id)
    {
        $request->validate([
            'clock_in'          => ['required', 'date_format:H:i'],
            'clock_out'         => ['required', 'date_format:H:i', 'after:clock_in'],
            'breaks.*.start'    => ['nullable', 'date_format:H:i'],
            'breaks.*.end'      => ['nullable', 'date_format:H:i', 'after:breaks.*.start'],
            'remarks'           => ['nullable', 'string', 'max:255'],
        ]);

        /* ここで実際は保存 → 今はダミーでメッセージだけ */
        return back()->with('success', '更新しました（ダミー保存）');
    }
}
