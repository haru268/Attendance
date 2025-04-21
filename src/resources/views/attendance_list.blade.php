{{-- resources/views/attendance_list.blade.php --}}
@extends('layouts.app')

@section('title', '勤怠一覧')

@section('content')
  <h2>勤怠一覧</h2>

  <div class="nav-links">
    <a href="{{ route('attendance.list', $prev) }}">先月</a>
    <span class="current-month">{{ $currentMonth }}</span>
    <a href="{{ route('attendance.list', $next) }}">翌月</a>
  </div>

  @if($noRecords)
    <p>打刻記録がありません。</p>
  @else
    @php
      // 曜日配列（日=0～土=6）
      $weekdays = ['日','月','火','水','木','金','土'];
    @endphp

    <table>
      <thead>
        <tr>
          <th>日付</th>
          <th>出勤</th>
          <th>退勤</th>
          <th>休憩</th>
          <th>合計</th>
          <th>詳細</th>
        </tr>
      </thead>
      <tbody>
        @foreach($attendances as $att)
          <tr>
            <td>
              {{ $att->date->format('m/d') }}（{{ $weekdays[$att->date->dayOfWeek] }}）
            </td>
            <td>{{ $att->clockIn }}</td>
            <td>{{ $att->clockOut }}</td>
            <td>{{ $att->breakTime }}</td>
            <td>{{ $att->totalTime }}</td>
            <td>
              <a href="{{ route('attendance.detail', ['id' => $att->date->format('Ymd')]) }}">
                詳細
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif
@endsection
