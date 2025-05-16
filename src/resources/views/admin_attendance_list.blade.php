{{-- resources/views/admin_attendance_list.blade.php --}}
@php
    // 表示対象日
    $sel = \Carbon\Carbon::parse(request('date', now()));
@endphp

@extends('layouts.app')

@section('title', $sel->format('Y年n月j日') . ' の勤怠')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@endpush

@section('content')
<div class="adminatt-container">

  {{-- ヘッダー --}}
  <div class="adminatt-header">
    <span class="adminatt-bar"></span>
    <h2>{{ $sel->format('Y年n月j日') }} の勤怠</h2>
  </div>

  {{-- 日付ナビ --}}
  <div class="adminatt-daybox">
    <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}"
       class="adminatt-daylink prev">
      <img src="{{ asset('img/arrow.png') }}" alt="前日" class="adminatt-nav-icon">
      前日
    </a>

    <span class="adminatt-daybox-date">
      <img src="{{ asset('img/calendar.png') }}" alt="カレンダー" class="adminatt-nav-icon">
      {{ $currentDateDisplay }}
    </span>

    <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}"
       class="adminatt-daylink next">
      翌日
      <img src="{{ asset('img/arrow.png') }}" alt="翌日" class="adminatt-nav-icon rotated">
    </a>
  </div>

  {{-- テーブル --}}
  <table class="adminatt-table">
    <thead>
      <tr>
        <th>名前</th>
        <th>出勤</th>
        <th>退勤</th>
        <th>休憩</th>
        <th>合計</th>
        <th>詳細</th>
      </tr>
    </thead>
    <tbody>
      @forelse($attendances as $a)
        @php
            // ID があればそれを、なければ日付文字列をキーに
            $key = $a->id ?? $a->created_at->format('Y-m-d');
        @endphp
        <tr>
          <td>{{ $a->user->name }}</td>
          <td>{{ $a->clockIn }}</td>
          <td>{{ $a->clockOut }}</td>
          <td>{{ $a->breakTime }}</td>
          <td>{{ $a->totalTime }}</td>
          <td>
            <a href="{{ route('admin.attendance.detail', ['key' => $key]) }}?staff_id={{ $a->user->id }}"
               class="adminatt-link">
              詳細
            </a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="adminatt-no-data text-center">
            打刻データがありません。
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>

</div>
@endsection
