@extends('layouts.app')

@section('title', '勤怠詳細')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endpush

@section('content')
@php
    $dt        = \Carbon\Carbon::parse($detail->date);
    $isPending = session('pending', false);
    // 実データの休憩のみ使用
    $breaks    = $detail->breaks ?? [];
@endphp

<div class="detail-container">
    <div class="detail-header"><h2>勤怠詳細</h2></div>

    <form action="{{ route('attendance.update', $detail->id) }}" method="POST">
        @csrf @method('PATCH')

        <table class="detail-table">
            <tbody>
                {{-- 名前 --}}
                <tr>
                    <th>名前</th>
                    <td>
                        <span class="name-last">{{ optional($detail->user)->last_name }}</span>
                        <span class="name-first">{{ optional($detail->user)->first_name }}</span>
                    </td>
                </tr>

                {{-- 日付 --}}
                <tr>
                    <th>日付</th>
                    <td class="date-wrap">
                        <span class="date-year">{{ $dt->format('Y年') }}</span>
                        <span class="date-month">{{ $dt->format('n月') }}</span>
                        <span class="date-day">{{ $dt->format('j日') }}</span>
                    </td>
                </tr>

                {{-- 出勤・退勤 --}}
                <tr>
                    <th>出勤・退勤</th>
                    <td class="flex-row">
                        @if ($isPending)
                            <span class="time-text">{{ $detail->clockIn }}</span>
                            <span class="tilde bold">〜</span>
                            <span class="time-text">{{ $detail->clockOut }}</span>
                        @else
                            <input type="text" name="clock_in" value="{{ $detail->clockIn }}" class="time-input">
                            <span class="tilde">〜</span>
                            <input type="text" name="clock_out" value="{{ $detail->clockOut }}" class="time-input">
                        @endif
                    </td>
                </tr>

                {{-- 休憩行（実データ分のみ） --}}
                @forelse ($breaks as $i => $br)
                <tr>
                    <th>{{ $i === 0 ? '休憩' : '休憩'.($i + 1) }}</th>
                    <td class="flex-row">
                        <input type="text" name="breaks[{{ $i }}][start]" value="{{ $br['start'] }}" class="time-input" {{ $isPending ? 'disabled' : '' }}>
                        <span class="tilde">〜</span>
                        <input type="text" name="breaks[{{ $i }}][end]" value="{{ $br['end'] }}" class="time-input" {{ $isPending ? 'disabled' : '' }}>
                    </td>
                </tr>
                @empty
                <tr>
                    <th>休憩</th>
                    <td class="flex-row">
                        <input type="text" name="breaks[0][start]" class="time-input" {{ $isPending ? 'disabled' : '' }}>
                        <span class="tilde">〜</span>
                        <input type="text" name="breaks[0][end]" class="time-input" {{ $isPending ? 'disabled' : '' }}>
                    </td>
                </tr>
                @endforelse

                {{-- 備考 --}}
                <tr>
                    <th>備考</th>
                    <td>
                        @if ($isPending)
                            <span class="remarks-text">{{ $detail->remarks }}</span>
                        @else
                            <textarea name="remarks" rows="3" class="remarks-input">{{ $detail->remarks }}</textarea>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="btn-area{{ $isPending ? ' pending' : '' }}">
            @if ($isPending)
                <p class="pending-note">＊承認待ちのため修正はできません。</p>
            @else
                <button type="submit" class="btn-update">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection
