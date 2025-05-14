@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
<style>
  /* エラーサマリーの文字色を赤に */
  .error-summary .error-message {
    color: red;
    margin-bottom: .5em;
  }
</style>
@endpush

@section('content')
<div class="detail-container">

  <div class="detail-header">
    <h2>勤怠詳細</h2>
  </div>

  {{-- 成功メッセージ --}}
  @if(session('success'))
    <div class="success-message">{{ session('success') }}</div>
  @endif

  {{-- 名前行の上にまとめてエラーを赤字で表示 --}}
  @if($errors->any())
    @php
      // メッセージを一意化する
      $uniqueMessages = array_unique($errors->all());
    @endphp

    <div class="error-summary">
      @foreach($uniqueMessages as $message)
        <div class="error-message">{{ $message }}</div>
      @endforeach
    </div>
  @endif

  <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
    @csrf
    @method('PATCH')

    <table class="detail-table">
      <tbody>
        {{-- 名前 --}}
        <tr>
          <th>名前</th>
          <td>{{ optional($attendance->user)->name }}</td>
        </tr>

        {{-- 日付 --}}
        <tr>
          <th>日付</th>
          <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') }}</td>
        </tr>

        {{-- 出勤・退勤 --}}
        <tr>
          <th>出勤・退勤</th>
          <td class="flex-row">
            <input
              type="text"
              name="clock_in"
              value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}"
              class="time-input"
              {{ $isPending ? 'disabled' : '' }}>
            <span class="tilde">〜</span>
            <input
              type="text"
              name="clock_out"
              value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}"
              class="time-input"
              {{ $isPending ? 'disabled' : '' }}>
          </td>
        </tr>

        {{-- 休憩 --}}
        @foreach($attendance->breakRecords as $i => $break)
        <tr>
          <th>{{ $i === 0 ? '休憩' : '休憩'.($i + 1) }}</th>
          <td class="flex-row">
            <input
              type="text"
              name="breaks[{{ $i }}][start]"
              value="{{ old("breaks.$i.start", optional($break->break_start)->format('H:i')) }}"
              class="time-input"
              {{ $isPending ? 'disabled' : '' }}>
            <span class="tilde">〜</span>
            <input
              type="text"
              name="breaks[{{ $i }}][end]"
              value="{{ old("breaks.$i.end", optional($break->break_end)->format('H:i')) }}"
              class="time-input"
              {{ $isPending ? 'disabled' : '' }}>
          </td>
        </tr>
        @endforeach

        {{-- 備考 --}}
        <tr>
          <th>備考</th>
          <td>
            @if($isPending)
              <span class="remarks-text">{{ $attendance->remarks }}</span>
            @else
              <textarea
                name="remarks"
                class="remarks-input"
                rows="3">{{ old('remarks', $attendance->remarks) }}</textarea>
            @endif
          </td>
        </tr>
      </tbody>
    </table>

    <div class="btn-area">
      @if ($isPending)
        <p class="pending-note">＊承認待ちのため修正はできません。</p>
      @else
        <button type="submit" class="btn-update">修正</button>
      @endif
    </div>
  </form>
</div>
@endsection
