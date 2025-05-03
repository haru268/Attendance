{{-- resources/views/attendance_detail.blade.php --}}
@extends('layouts.app')

@section('title','勤怠詳細')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endpush

@section('content')
@php
    $dt = \Carbon\Carbon::parse($detail->date);
    $isPending = session('pending', false);
    $breaks    = $detail->breaks ?? [];
@endphp

<div class="detail-container">
  <div class="detail-header"><h2>勤怠詳細</h2></div>

  <form action="{{ route('attendance.update',$detail->id) }}" method="POST">
    @csrf @method('PATCH')

    <table class="detail-table">
      <tbody>
        {{-- 名前（表示のみ） --}}
        <tr>
          <th>名前</th>
          <td>{{ optional($detail->user)->last_name }} {{ optional($detail->user)->first_name }}</td>
        </tr>

        {{-- 日付（表示のみ） --}}
        <tr>
          <th>日付</th>
          <td>{{ $dt->format('Y年 n月 j日') }}</td>
        </tr>

        {{-- 出勤・退勤 --}}
        <tr>
          <th>出勤・退勤</th>
          <td class="flex-row">
            <input type="text" name="clock_in"
                   value="{{ old('clock_in',$detail->clockIn) }}"
                   class="time-input" {{ $isPending?'disabled':'' }}>
            <span class="tilde">〜</span>
            <input type="text" name="clock_out"
                   value="{{ old('clock_out',$detail->clockOut) }}"
                   class="time-input" {{ $isPending?'disabled':'' }}>
          </td>
        </tr>
        @error('clock_in')  <tr><td colspan="2" class="error">{{ $message }}</td></tr>@enderror
        @error('clock_out') <tr><td colspan="2" class="error">{{ $message }}</td></tr>@enderror

        {{-- 休憩 --}}
        @foreach ($breaks as $i => $br)
        <tr>
          <th>{{ $i===0 ? '休憩' : '休憩'.($i+1) }}</th>
          <td class="flex-row">
            <input type="text" name="breaks[{{ $i }}][start]"
                   value="{{ old("breaks.$i.start",$br['start']) }}"
                   class="time-input" {{ $isPending?'disabled':'' }}>
            <span class="tilde">〜</span>
            <input type="text" name="breaks[{{ $i }}][end]"
                   value="{{ old("breaks.$i.end",$br['end']) }}"
                   class="time-input" {{ $isPending?'disabled':'' }}>
          </td>
        </tr>
        @endforeach
        {{-- 休憩エラーまとめて --}}
        @error('breaks.*.*') <tr><td colspan="2" class="error">{{ $message }}</td></tr>@enderror

        {{-- 備考 --}}
        <tr>
          <th>備考</th>
          <td>
            @if ($isPending)
              <span class="remarks-text">{{ $detail->remarks }}</span>
            @else
              <textarea name="remarks" class="remarks-input"
                        rows="3">{{ old('remarks',$detail->remarks) }}</textarea>
            @endif
          </td>
        </tr>
        @error('remarks') <tr><td colspan="2" class="error">{{ $message }}</td></tr>@enderror
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
