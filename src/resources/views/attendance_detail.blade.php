{{-- resources/views/attendance_detail.blade.php ----------------------------------}}
@extends('layouts.app')

@section('title','勤怠詳細')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endpush

@section('content')
@php
    use Illuminate\Support\Facades\Auth;
    use Carbon\Carbon;

    $dt      = Carbon::parse($detail->date);
    $isDummy = is_null($detail->id);
    $isAdmin = Auth::user()->is_admin;
@endphp

<div class="detail-container">
    <div class="detail-header">
        <h2>勤怠詳細</h2>
    </div>

    <form
        action="{{ $isAdmin
            ? route('admin.revision.approve', ['id' => $detail->id])
            : route('attendance.update',    ['id' => $detail->id]) }}"
        method="POST">
        @csrf

        <table class="detail-table">
            <tbody>
                <tr>
                    <th>名前</th>
                    <td>{{ optional($detail->user)->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>{{ $dt->format('Y年 n月 j日') }}</td>
                </tr>

                <tr>
                    <th>出勤・退勤</th>
                    <td class="flex-row">
                        <input name="clock_in"  class="time-input" type="text"
                               value="{{ old('clock_in', optional($detail->clock_in)->format('H:i')) }}"
                               {{ $isAdmin ? '' : 'disabled' }}>
                        <span class="tilde">〜</span>
                        <input name="clock_out" class="time-input" type="text"
                               value="{{ old('clock_out', optional($detail->clock_out)->format('H:i')) }}"
                               {{ $isAdmin ? '' : 'disabled' }}>
                    </td>
                </tr>

                @foreach($detail->breaks as $i => $break)
                    <tr>
                        <th>休憩{{ $i + 1 }}</th>
                        <td class="flex-row">
                            <input name="breaks[{{ $i }}][start]" class="time-input" type="text"
                                   value="{{ old("breaks.$i.start", $break['start']) }}"
                                   {{ $isAdmin ? '' : 'disabled' }}>
                            <span class="tilde">〜</span>
                            <input name="breaks[{{ $i }}][end]"   class="time-input" type="text"
                                   value="{{ old("breaks.$i.end",   $break['end']) }}"
                                   {{ $isAdmin ? '' : 'disabled' }}>
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="remarks"
                                  rows="3"
                                  class="remarks-input"
                                  {{ $isAdmin ? '' : 'disabled' }}>{{ old('remarks', $detail->remarks) }}</textarea>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="btn-area">
            @if($isAdmin)
                {{-- 管理者用：承認ボタン --}}
                <button type="submit" class="btn btn-primary">
                    承認
                </button>
            @elseif(!$hasPending)
                {{-- 一般ユーザー用：修正申請ボタン --}}
                <button type="submit" class="btn-update">
                    修正
                </button>
            @endif
        </div>
    </form>
</div>
@endsection