{{-- resources/views/attendance.blade.php --}}
@extends('layouts.app')

@section('title', '勤怠登録')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endpush

@section('content')
<div class="attendance-container">
  {{-- ステータス --}}
  <span class="status-badge" id="statusBadge">勤務外</span>

  {{-- 日付表示 --}}
  <div class="date-display" id="dateDisplay"></div>

  {{-- 時刻表示 --}}
  <div class="time-display" id="timeDisplay"></div>

  {{-- ボタンエリア --}}
  <div id="buttonArea">
    <button type="button" class="btn-clock-in" id="btnClockIn">出勤</button>
  </div>
</div>
@endsection

@push('scripts')
<script>
function updateDateTime() {
  const now = new Date();
  const wdays = ['日','月','火','水','木','金','土'];
  const Y = now.getFullYear(),
        M = now.getMonth() + 1,
        D = now.getDate(),
        W = wdays[now.getDay()];
  document.getElementById('dateDisplay').textContent = `${Y}年${M}月${D}日（${W}）`;
  const h = String(now.getHours()).padStart(2,'0'),
        m = String(now.getMinutes()).padStart(2,'0');
  document.getElementById('timeDisplay').textContent = `${h}:${m}`;
}
updateDateTime();
setInterval(updateDateTime, 60000);

function showClockButtons() {
  buttonArea.innerHTML = `
    <button type="button" class="btn-clock-out" id="btnClockOut">退勤</button>
    <button type="button" class="btn-break-in" id="btnBreakIn">休憩入</button>
  `;
  document.getElementById('btnClockOut').addEventListener('click', onClockOut);
  document.getElementById('btnBreakIn').addEventListener('click', onBreakIn);
}

function onClockOut() {
  statusBadge.textContent = '退勤済';
  buttonArea.innerHTML = `<div class="completed-message">お疲れ様でした。</div>`;
}

function onBreakIn() {
  statusBadge.textContent = '休憩中';
  buttonArea.innerHTML = `
    <button type="button" class="btn-break-in" id="btnBreakReturn">休憩戻</button>
  `;
  document.getElementById('btnBreakReturn').addEventListener('click', onBreakReturn);
}

function onBreakReturn() {
  statusBadge.textContent = '出勤中';
  showClockButtons();
}

const statusBadge = document.getElementById('statusBadge');
const buttonArea   = document.getElementById('buttonArea');
document.getElementById('btnClockIn').addEventListener('click', () => {
  statusBadge.textContent = '出勤中';
  showClockButtons();
});
</script>
@endpush
