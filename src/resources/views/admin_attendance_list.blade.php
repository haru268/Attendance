{{-- resources/views/admin_attendance_list.blade.php --}}
@php
    /** @var \Carbon\Carbon $sel */
    $sel = \Carbon\Carbon::parse(request('date', now()));
    // ダミー対象スタッフのID→名前マップ
    $dummyStaffNames = [
        1 => '山田 太郎',
        2 => '西 伶奈',
        3 => '増田 一世',
        4 => '山本 敬吉',
        5 => '秋田 朋美',
        6 => '中西 教夫',
    ];
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

    {{-- ▼ 日付ナビ --}}
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

    {{-- ▼ 一覧テーブル --}}
    <table class="adminatt-table">
        <thead>
            <tr>
                <th>名前</th><th>出勤</th><th>退勤</th>
                <th>休憩</th><th>合計</th><th>詳細</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($attendances as $a)
            @php
                // リンク用 key は、実データならID、ダミーなら日付文字列
                $key = $a->id
                       ? $a->id
                       : $a->created_at->format('Y-m-d');

                // スタッフIDは、実データなら $a->user->id、
                // ダミーなら名前からマップを逆引き
                if (! empty($a->user->id)) {
                    $staffId = $a->user->id;
                } else {
                    $staffId = array_search($a->user->name, $dummyStaffNames, true) ?: null;
                }
            @endphp
            <tr>
                <td>{{ optional($a->user)->name }}</td>
                <td>{{ $a->clockIn  }}</td>
                <td>{{ $a->clockOut }}</td>
                <td>{{ $a->breakTime }}</td>
                <td>{{ $a->totalTime }}</td>
                <td>
                    @if($staffId)
                      {{-- 必ず staff_id クエリを付与 --}}
                      <a href="{{ route('admin.attendance.detail', ['key' => $key]) }}?staff_id={{ $staffId }}"
                         class="adminatt-link">
                          詳細
                      </a>
                    @else
                      ―
                    @endif
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
