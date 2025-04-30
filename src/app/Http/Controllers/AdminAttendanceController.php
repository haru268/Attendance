<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', Carbon::now()->format('Y-m-d'));
        $selected = Carbon::parse($date);
        $todayStr = Carbon::now()->format('Y-m-d');

        $prevDate = $selected->copy()->subDay()->format('Y-m-d');
        $nextDate = $selected->copy()->addDay()->format('Y-m-d');

        if ($date <= $todayStr) {
            $attendances = Attendance::with('user')
                ->whereDate('created_at', $date)
                ->get();
        } else {
            $attendances = collect(); 
        }

        return view('admin_attendance_list', compact(
            'attendances','date','prevDate','nextDate'
        ));
    }

    public function detail($id)
    {
        $attendance = Attendance::with('user','breakRecords')
            ->findOrFail($id);
        return view('admin_attendance_detail', compact('attendance'));
    }
}
