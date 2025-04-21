{{-- resources/views/admin_attendance_staff.blade.php --}}
@extends('layouts.app')

@section('title', $staff->name . 'さんの勤怠一覧')

@section('content')
  <h2>{{ $staff->name }} さんの勤怠一覧（{{ $currentDateDisplay }}）</h2>
  <div>
    <a href="{{ route('admin.attendance.staff', ['id'=>$staff->id, 'date'=>$prevDate]) }}">前月</a>
    |
    <a href="{{ route('admin.attendance.staff', ['id'=>$staff->id, 'date'=>$nextDate]) }}">翌月</a>
  </div>
  <table>
    <thead>
      <tr>
        <th>日付</th><th>出勤</th><th>退勤</th><th>休憩</th><th>合計</th><th>詳細</th>
      </tr>
    </thead>
    <tbody>
      @forelse($attendances as $att)
        <tr>
          <td>{{ $att->created_at->format('m月d日 (') . ['日','月','火','水','木','金','土'][$att->created_at->dayOfWeek] . ')' }}</td>
          <td>{{ $att->clockIn }}</td>
          <td>{{ $att->clockOut }}</td>
          <td>{{ $att->breakTime }}</td>
          <td>{{ $att->totalTime }}</td>
          <td><a href="{{ route('admin.attendance.detail', $att->id) }}?staff_id={{ $staff->id }}">詳細</a></td>
        </tr>
      @empty
        <tr><td colspan="6">該当する勤怠データがありません。</td></tr>
      @endforelse
    </tbody>
  </table>
@endsection
