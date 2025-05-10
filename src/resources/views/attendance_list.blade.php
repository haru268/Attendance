{{-- resources/views/attendance_list.blade.php --}}
@extends('layouts.app')

@section('title', '勤怠一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endpush

@section('content')
<div class="attendance-list-container">
  {{-- タイトル --}}
  <div class="attendance-list-header">
    <h2>勤怠一覧</h2>
  </div>

  {{-- 月切り替えナビ --}}
  <div class="month-nav">
    <a href="{{ route('attendance.list', ['year' => $prev->year, 'month' => $prev->month]) }}" class="prev">
      <img src="{{ asset('img/arrow.png') }}" alt="前月" class="nav-icon">
      先月
    </a>

    <span class="current-month">
      <img src="{{ asset('img/calendar.png') }}" alt="カレンダー" class="nav-icon">
      {{ $currentMonth }}
    </span>

    <a href="{{ route('attendance.list', ['year' => $next->year, 'month' => $next->month]) }}" class="next">
      翌月
      <img src="{{ asset('img/arrow.png') }}" alt="翌月" class="nav-icon rotated">
    </a>
  </div>

  @if($noRecords)
    <p class="no-records">記録がありません</p>
  @else
    <table class="attendance-table">
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
            {{ $att->date->format('m/d') }}（{{ ['日','月','火','水','木','金','土'][$att->date->dayOfWeek] }}）
          </td>
          <td>{{ $att->clockIn   ?? '-' }}</td>
          <td>{{ $att->clockOut  ?? '-' }}</td>
          <td>{{ $att->breakTime ?? '-' }}</td>
          <td>{{ $att->totalTime ?? '-' }}</td>
          <td>
            <a href="{{ route('attendance.detail', ['id' => $att->date->format('Ymd')]) }}"
               class="detail-link">詳細</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>
@endsection
