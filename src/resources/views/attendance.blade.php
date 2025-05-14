{{-- resources/views/attendance.blade.php --}}
@extends('layouts.app')

@section('title','勤怠登録')

@section('content')
<div class="attendance-container">

    {{-- ステータス表示 --}}
    <span class="status-badge">{{ $status }}</span>

    {{-- 今日の日付と時刻 --}}
    <div id="dateDisplay" class="date-display"></div>
    <div id="timeDisplay" class="time-display"></div>

    {{-- ボタンエリア --}}
    <div id="buttonArea">
        @switch($status)

            @case('勤務外')
                <form method="POST" action="{{ route('attendance.clock') }}">
                    @csrf
                    <input type="hidden" name="type" value="clock_in">
                    <button type="submit" class="btn-clock btn-clock-in">
                        出勤
                    </button>
                </form>
            @break

            @case('出勤中')
                <form method="POST" action="{{ route('attendance.clock') }}" style="display:inline-block">
                    @csrf
                    <input type="hidden" name="type" value="clock_out">
                    <button type="submit" class="btn-clock btn-clock-out">
                        退勤
                    </button>
                </form>
                <form method="POST" action="{{ route('attendance.clock') }}" style="display:inline-block">
                    @csrf
                    <input type="hidden" name="type" value="break_in">
                    <button type="submit" class="btn-clock btn-break-in">
                        休憩入
                    </button>
                </form>
            @break

            @case('休憩中')
                <form method="POST" action="{{ route('attendance.clock') }}">
                    @csrf
                    <input type="hidden" name="type" value="break_out">
                    <button type="submit" class="btn-clock btn-break-out">
                        休憩戻
                    </button>
                </form>
            @break

            @default
                <div class="completed-message">お疲れ様でした。</div>
        @endswitch
    </div><!-- /#buttonArea -->

</div><!-- /.attendance-container -->
@endsection

@push('scripts')
<script>
/* -------- 時計表示 -------- */
const JP_WEEK = ['日','月','火','水','木','金','土'];
function tick(){
    const n = new Date();
    document.getElementById('dateDisplay').textContent =
        `${n.getFullYear()}年${n.getMonth()+1}月${n.getDate()}日（${JP_WEEK[n.getDay()]}）`;
    document.getElementById('timeDisplay').textContent =
        n.toLocaleTimeString('ja-JP',{hour:'2-digit',minute:'2-digit'});
}
tick();
setInterval(tick, 60_000);
</script>
@endpush
