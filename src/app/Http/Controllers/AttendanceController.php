<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('attendance');
    }

    public function detail($id)
    {
        $dateObj = \Carbon\Carbon::createFromFormat('Ymd', $id);

        $breaks = $dateObj->day % 2
            ? [['start'=>'12:00','end'=>'13:00']]
            : [['start'=>'12:00','end'=>'12:45'],['start'=>'15:00','end'=>'15:15']];

        $user = (object)[
            'last_name'  => '山田',
            'first_name' => '太郎',
            'name'       => '山田 太郎',
        ];

        $detail = (object)[
            'id'       => $id,
            'date'     => $dateObj->toDateString(),
            'clockIn'  => '09:00',
            'clockOut' => '18:00',
            'breaks'   => $breaks,
            'remarks'  => '特になし',
            'user'     => $user,
        ];

        return view('attendance_detail', compact('detail'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'clock_in'  => ['required','date_format:H:i'],
            'clock_out' => ['required','date_format:H:i','after:clock_in'],
            'breaks.*.start' => ['nullable','date_format:H:i'],
            'breaks.*.end'   => ['nullable','date_format:H:i','after:breaks.*.start'],
            'remarks'   => ['nullable','string','max:255'],
        ]);

        return back()->with('pending', true);

    }
}
