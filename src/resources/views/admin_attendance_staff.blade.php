{{-- resources/views/admin_attendance_staff.blade.php --}}
@php
    /**
     * $staff                … 対象スタッフ（id / name / email）
     * $currentDateDisplay   … 「YYYY/MM」形式の表示用文字列
     * $prevDate / $nextDate … 前月・翌月へ飛ぶ YYYY-MM-01 形式
     * $attendances          … その月の勤怠コレクション
     */
@endphp

@extends('layouts.app')

@section('title', $staff->name.' さんの勤怠一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_staff.css') }}">
@endpush

@section('content')
<div class="adminattstaff-container">
    {{-- ▲ ヘッダー --}}
    <div class="adminattstaff-header">
        <span class="adminattstaff-bar"></span>
        <h2>{{ $staff->name }} さんの勤怠一覧</h2>
    </div>

    {{-- ▼ 月ナビ --}}
    <div class="adminattstaff-monthbox">
        <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'date' => $prevDate]) }}"
           class="adminattstaff-monthlink prev">
           <img src="{{ asset('img/arrow.png') }}" alt="前月" class="adminattstaff-icon">
           前月
        </a>

        <span class="adminattstaff-monthbox-date">
            <img src="{{ asset('img/calendar.png') }}" alt="カレンダー" class="adminattstaff-icon">
            {{ $currentDateDisplay }}
        </span>

        <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'date' => $nextDate]) }}"
           class="adminattstaff-monthlink next">
           翌月
           <img src="{{ asset('img/arrow.png') }}" alt="翌月" class="adminattstaff-icon rotated">
        </a>
    </div>

    {{-- ▼ 勤怠テーブル --}}
    <div class="adminattstaff-card">
        <table class="adminattstaff-table">
            <thead>
                <tr>
                    <th>日付</th><th>出勤</th><th>退勤</th><th>休憩</th><th>合計</th><th>詳細</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($attendances as $att)
                <tr>
                    <td>
                        {{ \Carbon\Carbon::parse($att->created_at)->format('m/d') }}
                        （{{ ['日','月','火','水','木','金','土'][\Carbon\Carbon::parse($att->created_at)->dayOfWeek] }}）
                    </td>
                    <td>{{ $att->clockIn   }}</td>
                    <td>{{ $att->clockOut  }}</td>
                    <td>{{ $att->breakTime }}</td>
                    <td>{{ $att->totalTime }}</td>
                    <td>
                        {{-- 管理者用勤怠詳細へ --}}
                        <a href="{{ route('admin.attendance.detail', ['id' => $att->id]) }}"
                           class="adminattstaff-link">
                            詳細
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="no-records">該当する勤怠データがありません。</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
