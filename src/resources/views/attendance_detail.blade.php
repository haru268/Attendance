@extends('layouts.app')

@section('title','勤怠詳細')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endpush

@section('content')
@php
    $dt        = \Carbon\Carbon::parse($detail->date);
    $isPending = session('pending', false);
    $breaks    = $detail->breaks ?? [];
    // フルネームをどちらかから取得
    if (isset($detail->user->name)) {
        $fullName = $detail->user->name;
    } else {
        $fullName = trim(($detail->user->last_name ?? '') . ' ' . ($detail->user->first_name ?? ''));
    }
@endphp

<div class="detail-container">
  <div class="detail-header"><h2>勤怠詳細</h2></div>

  {{-- 修正申請フォーム（承認待ち以外なら常に表示） --}}
  @if (! $isPending)
    <form action="{{ route('attendance.update',['id'=>$detail->id]) }}" method="POST">
      @csrf @method('PATCH')
  @endif

      <table class="detail-table">
        <tbody>
          {{-- 名前 --}}
          <tr>
            <th>名前</th>
            <td>{{ $fullName }}</td>
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
              <input type="text" name="clock_in"
                     value="{{ old('clock_in',$detail->clockIn) }}"
                     class="time-input"
                     {{ $isPending ? 'disabled' : '' }}>
              <span class="tilde">〜</span>
              <input type="text" name="clock_out"
                     value="{{ old('clock_out',$detail->clockOut) }}"
                     class="time-input"
                     {{ $isPending ? 'disabled' : '' }}>
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
                     class="time-input"
                     {{ $isPending ? 'disabled' : '' }}>
              <span class="tilde">〜</span>
              <input type="text" name="breaks[{{ $i }}][end]"
                     value="{{ old("breaks.$i.end",$br['end']) }}"
                     class="time-input"
                     {{ $isPending ? 'disabled' : '' }}>
            </td>
          </tr>
          @endforeach
          @error('breaks.*.*') <tr><td colspan="2" class="error">{{ $message }}</td></tr>@enderror

          {{-- 備考 --}}
          <tr>
            <th>備考</th>
            <td>
              <textarea name="remarks"
                        class="remarks-input"
                        rows="3"
                        {{ $isPending ? 'disabled' : '' }}>
                {{ old('remarks',$detail->remarks) }}
              </textarea>
            </td>
          </tr>
          @error('remarks') <tr><td colspan="2" class="error">{{ $message }}</td></tr>@enderror
        </tbody>
      </table>

  @if (! $isPending)
      <div class="btn-area">
        <button type="submit" class="btn-update">修正</button>
      </div>
    </form>
  @endif

</div>
@endsection
