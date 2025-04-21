{{-- resources/views/attendance.blade.php --}}
@extends('layouts.app')

@section('title', '勤怠登録')

@section('content')
<div class="attendance__status">
  <h2 id="attendanceStatus">勤務外</h2>
  <p id="currentDate"></p>
  <p id="currentTime"></p>
  <div id="buttonContainer">
    <button type="button" id="clockInButton">出勤</button>
  </div>
</div>
@endsection

@section('scripts')
<script>
// JavaScript for clockIn, clockOut, breakIn, breakOut
// 省略: 先程実装したリアルタイム表示コードをここに貼り込んでください
</script>
@endsection