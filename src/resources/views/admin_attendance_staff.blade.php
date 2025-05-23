{{-- resources/views/admin_attendance_staff.blade.php --}}
@php
    // 表示対象年月を Carbon でパース
    $monthTop     = \Carbon\Carbon::parse(request('date', now()->startOfMonth()));
    $currentMonth = $monthTop->format('Y/m');
@endphp

@extends('layouts.app')

@section('title', $staff->name . 'さんの勤怠一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_staff.css') }}">
@endpush

@section('content')
<div class="adminattstaff-container">

    {{-- ▲ 見出し --}}
    <div class="adminattstaff-header">
        <span class="adminattstaff-bar"></span>
        <h2>{{ $staff->name }}さんの勤怠一覧</h2>
    </div>

    {{-- ▼ 月切り替えナビ --}}
    <div class="adminattstaff-monthbox">
        <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'date' => $prevDate]) }}"
           class="adminattstaff-monthlink prev">
            <img src="{{ asset('img/arrow.png') }}" alt="前月" class="adminattstaff-icon">
            前月
        </a>

        <span class="adminattstaff-monthbox-date">
            <img src="{{ asset('img/calendar.png') }}" alt="カレンダー" class="adminattstaff-icon">
            {{ $currentMonth }}
        </span>

        <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'date' => $nextDate]) }}"
           class="adminattstaff-monthlink next">
            翌月
            <img src="{{ asset('img/arrow.png') }}" alt="翌月" class="adminattstaff-icon rotated">
        </a>
    </div>

    {{-- ▼ 白背景カード --}}
    <div class="adminattstaff-card">
        <table class="adminattstaff-table">
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
            @forelse ($attendances as $att)
                <tr>
                    <td>
                        {{ $att->created_at->format('m/d') }}
                        （{{ ['日','月','火','水','木','金','土'][$att->created_at->dayOfWeek] }}）
                    </td>
                    <td>{{ $att->clockIn }}</td>
                    <td>{{ $att->clockOut }}</td>
                    <td>{{ $att->breakTime }}</td>
                    <td>{{ $att->totalTime }}</td>
                    <td>
                        @php
                            $key = $att->id ?: $att->created_at->format('Y-m-d');
                        @endphp
                        <a href="{{ route('admin.attendance.detail', ['key' => $key]) }}?staff_id={{ $staff->id }}"
                           class="adminattstaff-link">
                            詳細
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">該当する勤怠データがありません。</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{-- ▼ CSV出力ボタン（右下） --}}
        <div style="text-align: right; margin-top: 1rem;">
            <a href="{{ route('admin.attendance.staff.export', ['id' => $staff->id]) }}?month={{ $monthTop->format('Y-m') }}"
               style="display: inline-block; padding: 0.5rem 1rem; background: #000; color: #fff; border-radius: 4px; text-decoration: none;">
                CSV出力
            </a>
        </div>

    </div>

</div>
@endsection
