{{-- resources/views/attendance_detail.blade.php --}}
@extends('layouts.app')

@section('title', '勤怠詳細')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endpush

@section('content')
@php
    // 日付とダミーフラグ判定
    $dt      = \Carbon\Carbon::parse($detail->date);
    $isDummy = is_null($detail->id);

    // 出勤／退勤の初期値（ダミー用）
    $dummyClockIn    = '09:00';
    $dummyClockOut   = '18:00';

    // 休憩の初期値（ダミー用）
    // もし複数休憩を作っているなら増やしてください
    $dummyBreaks     = [
        ['start' => '12:00', 'end' => '13:00'],
    ];

    // ビューに渡す breaks 配列
    $breaks = $isDummy
              ? $dummyBreaks
              : ($detail->breaks ?? []);
@endphp

<div class="detail-container">
  <div class="detail-header"><h2>勤怠詳細</h2></div>

  {{-- ダミーメモ --}}
  @if($isDummy)
    <p class="dummy-note">
      この勤怠はダミーデータのため、編集はできません。
    </p>
  @endif

  <form
    @unless($isDummy)
      action="{{ route('attendance.update', $detail->id) }}"
      method="POST"
    @endunless
  >
    @unless($isDummy)
      @csrf
      @method('PATCH')
    @endunless

    <table class="detail-table">
      <tbody>
        {{-- 名前 --}}
        <tr>
          <th>名前</th>
          <td>{{ $detail->user->name }}</td>
        </tr>

        {{-- 日付 --}}
        <tr>
          <th>日付</th>
          <td>{{ $dt->format('Y年 n月 j日') }}</td>
        </tr>

        {{-- 出勤・退勤 --}}
        <tr>
          <th>出勤・退勤</th>
          <td class="flex-row">
            <input
              type="text"
              name="clock_in"
              value="{{ old(
                'clock_in',
                $isDummy
                  ? $dummyClockIn
                  : (optional($detail->clock_in)->format('H:i') ?? '')
              ) }}"
              class="time-input"
              {{ $isDummy ? 'disabled' : '' }}
            >
            <span class="tilde">〜</span>
            <input
              type="text"
              name="clock_out"
              value="{{ old(
                'clock_out',
                $isDummy
                  ? $dummyClockOut
                  : (optional($detail->clock_out)->format('H:i') ?? '')
              ) }}"
              class="time-input"
              {{ $isDummy ? 'disabled' : '' }}
            >
          </td>
        </tr>
        @error('clock_in')
          <tr><td colspan="2" class="error">{{ $message }}</td></tr>
        @enderror
        @error('clock_out')
          <tr><td colspan="2" class="error">{{ $message }}</td></tr>
        @enderror

        {{-- 休憩 --}}
        @foreach($breaks as $i => $br)
          <tr>
            <th>休憩{{ $i + 1 }}</th>
            <td class="flex-row">
              <input
                type="text"
                name="breaks[{{ $i }}][start]"
                value="{{ old(
                  "breaks.$i.start",
                  $isDummy
                    ? $dummyBreaks[$i]['start']
                    : ($br['start'] ?? '')
                ) }}"
                class="time-input"
                {{ $isDummy ? 'disabled' : '' }}
              >
              <span class="tilde">〜</span>
              <input
                type="text"
                name="breaks[{{ $i }}][end]"
                value="{{ old(
                  "breaks.$i.end",
                  $isDummy
                    ? $dummyBreaks[$i]['end']
                    : ($br['end'] ?? '')
                ) }}"
                class="time-input"
                {{ $isDummy ? 'disabled' : '' }}
              >
            </td>
          </tr>
        @endforeach
        @error('breaks.*.*')
          <tr><td colspan="2" class="error">{{ $message }}</td></tr>
        @enderror

        {{-- 備考 --}}
        <tr>
          <th>備考</th>
          <td>
            <textarea
              name="remarks"
              class="remarks-input"
              rows="3"
              {{ $isDummy ? 'disabled' : '' }}
            >{{ old('remarks', $detail->remarks) }}</textarea>
          </td>
        </tr>
        @error('remarks')
          <tr><td colspan="2" class="error">{{ $message }}</td></tr>
        @enderror
      </tbody>
    </table>

    {{-- 修正ボタン --}}
    <div class="btn-area">
      <button
        type="submit"
        class="btn-update"
        {{ $isDummy ? 'disabled' : '' }}
      >
        修正
      </button>
    </div>
  </form>
</div>
@endsection
