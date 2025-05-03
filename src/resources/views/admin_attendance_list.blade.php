{{-- resources/views/admin_attendance_list.blade.php --}}
@php
    /** @var \Carbon\Carbon $sel */
    $sel = \Carbon\Carbon::parse(request('date', now()));   // 一覧に表示している日
@endphp

@extends('layouts.app')

@section('title', $sel->format('Y年n月j日') . ' の勤怠')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@endpush

@section('content')
<div class="adminatt-container">

    {{-- ▲ 見出し --}}
    <div class="adminatt-header">
        <span class="adminatt-bar"></span>
        <h2>{{ $sel->format('Y年n月j日') }} の勤怠</h2>
    </div>

    {{-- ▼ 日付ナビ（白カード） --}}
    <div class="adminatt-daybox">
        <a href="{{ route('admin.attendance.list', ['date'=>$sel->copy()->subDay()->toDateString()]) }}"
           class="adminatt-daylink prev">
           <img src="{{ asset('img/arrow.png.png') }}" alt="前日" class="adminatt-nav-icon">
           前日
        </a>

        <span class="adminatt-daybox-date">
            <img src="{{ asset('img/calendar.png.png') }}" alt="カレンダー" class="adminatt-nav-icon">
            {{ $sel->format('Y/m/d') }}
        </span>

        <a href="{{ route('admin.attendance.list', ['date'=>$sel->copy()->addDay()->toDateString()]) }}"
           class="adminatt-daylink next">
           翌日
           <img src="{{ asset('img/arrow.png.png') }}" alt="翌日" class="adminatt-nav-icon rotated">
        </a>
    </div>

    {{-- ▼ 一覧テーブル --}}
    <table class="adminatt-table">
        <thead>
            <tr>
                <th>名前</th><th>出勤</th><th>退勤</th><th>休憩</th><th>合計</th><th>詳細</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($attendances as $a)
            <tr>
                <td>{{ $a->user->name }}</td>
                <td>{{ $a->clockIn  }}</td>
                <td>{{ $a->clockOut }}</td>
                <td>{{ $a->breakTime }}</td>
                <td>{{ $a->totalTime }}</td>

                {{-- ▼ 変更点：クリックで /attendance/{yyyymmdd} へ遷移 --}}
                <td>
                    <a href="{{ url('/attendance/'.$sel->format('Ymd')) }}"
                       class="adminatt-link">詳細</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6">今日出勤している人はいません。</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
