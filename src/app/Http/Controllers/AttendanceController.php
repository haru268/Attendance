<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * 打刻トップ（打刻画面を返す）
     */
    public function index()
    {
        return view('attendance');
    }

    /**
     * 勤怠詳細（ID は “YYYYMMDD” フォーマットの日付）
     *
     * @param  string  $id
     */
    public function detail($id)
    {
        $dateObj = Carbon::parse($id); // 例: "20250502" → 2025-05-02

        // 休憩は常に１回分だけ固定で渡す
        $breaks = [
            ['start' => '12:00', 'end' => '13:00'],
        ];

        $detail = (object)[
            'id'       => $id,
            'date'     => $dateObj->toDateString(),
            'clockIn'  => '09:00',
            'clockOut' => '18:00',
            'breaks'   => $breaks,
            'remarks'  => '特になし',
            'user'     => (object)[
                'last_name'  => '山田',
                'first_name' => '太郎',
            ],
        ];

        return view('attendance_detail', compact('detail'));
    }

    /**
     * 勤怠更新（修正申請）
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'clock_in'       => ['required', 'date_format:H:i'],
            'clock_out'      => ['required', 'date_format:H:i', 'after:clock_in'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end'   => ['nullable', 'date_format:H:i', 'after:breaks.*.start'],
            'remarks'        => ['nullable', 'string', 'max:255'],
        ]);

        // 本来はモデル保存処理を入れるところですが、今はダミーでリダイレクト
        return back()->with('success', '更新しました（ダミー保存）');
    }
}
