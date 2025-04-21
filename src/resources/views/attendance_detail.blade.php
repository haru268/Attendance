{{-- resources/views/attendance_detail.blade.php --}}
@extends('layouts.app')

@section('title', '勤怠詳細')

@section('content')
  <h2 class="attendance__detail-title">勤怠詳細</h2>

  {{-- バリデーションエラー --}}
  @if ($errors->any())
    <div class="error-messages" style="color:red; margin-bottom:1em;">
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- フラッシュメッセージ（承認待ち） --}}
    @if ($errors->any())
    <div class="error-messages">
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('attendance.update', $detail->id) }}" method="POST" id="attendanceUpdateForm">
    @csrf

    <div class="attendance__detail-container">
      {{-- 名前／日付などの表示 --}}
      <p><strong>名前:</strong> {{ $detail->user->name ?? '不明' }}</p>
      <p><strong>日付:</strong> {{ $detail->date }}</p>

      {{-- 出勤・退勤 --}}
      <p>
        <strong>出勤・退勤:</strong>
        <input type="text" name="clockIn"  value="{{ old('clockIn',  $detail->clockIn)  }}">
        〜
        <input type="text" name="clockOut" value="{{ old('clockOut', $detail->clockOut) }}">
      </p>

      {{-- 休憩1 --}}
      <p>
        <strong>休憩1:</strong>
        <input type="text" name="breakStart[]" value="{{ old('breakStart.0', $detail->breaks[0]['start'] ?? '') }}">
        〜
        <input type="text" name="breakEnd[]"   value="{{ old('breakEnd.0',   $detail->breaks[0]['end']   ?? '') }}">
      </p>

      {{-- 備考 --}}
      <p>
        <strong>備考:</strong>
        <input type="text" name="remarks" value="{{ old('remarks', $detail->remarks) }}">
      </p>
    </div>

    <div class="attendance__detail-actions">
      {{-- pending セッションがあるときはボタンを消してメッセージだけ --}}
      @if (session('pending'))
        <p style="color:gray;">*承認待ちのため修正はできません。</p>
      @else
        <button type="submit" class="attendance__detail-correct-button">修正</button>
      @endif
    </div>
  </form>
@endsection
