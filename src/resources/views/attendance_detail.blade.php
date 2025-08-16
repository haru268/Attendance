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

    $dt             = Carbon::parse($detail->date);
    $isAdmin        = (bool) (Auth::user()->is_admin ?? false);
    $hasPending     = (bool) ($hasPending ?? false);
    $pending        = $pendingRequest ?? null;      // RevisionRequest or null
    $hasAttendance  = !empty($detail->id);          // この日の Attendance が存在するか

    // 管理者は常に編集可。一般は「レコードあり＆未承認」のときだけ編集可
    $canEdit        = $isAdmin ? true : ($hasAttendance && !$hasPending);

    // 送信先の決定（承認/一般の修正申請時のみ使用）
    $formAction = '#';
    $usePatch   = false;
    if ($isAdmin && $pending) {
        $formAction = route('admin.revision.approve', ['id' => $pending->id]); // 承認はPOST
    } elseif (!$isAdmin && $hasAttendance) {
        $formAction = route('attendance.update', ['id' => $detail->id]);       // 一般の修正申請はPATCH
        $usePatch   = true;
    }
@endphp

<div class="detail-container">
    <div class="detail-header">
        <h2>勤怠詳細</h2>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- ※ onsubmit ガードは付けない（formaction ボタンの送信を妨げないため） --}}
    <form action="{{ $formAction }}" method="POST">
        @csrf
        @if($usePatch)
            @method('PATCH')
        @endif

        {{-- 管理者の upsert 用に常に埋め込む --}}
        <input type="hidden" name="user_id" value="{{ optional($detail->user)->id }}">
        <input type="hidden" name="date"    value="{{ $dt->toDateString() }}">

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
                        <input
                            name="clock_in"
                            class="time-input"
                            type="time"
                            value="{{ old('clock_in', optional($detail->clock_in)->format('H:i')) }}"
                            {{ $canEdit ? '' : 'disabled' }}>
                        <span class="tilde">〜</span>
                        <input
                            name="clock_out"
                            class="time-input"
                            type="time"
                            value="{{ old('clock_out', optional($detail->clock_out)->format('H:i')) }}"
                            {{ $canEdit ? '' : 'disabled' }}>
                    </td>
                </tr>

                @php $breaks = $detail->breaks ?? []; @endphp

                {{-- 既存の休憩を表示 --}}
                @forelse($breaks as $i => $break)
                    <tr>
                        <th>休憩{{ $i + 1 }}</th>
                        <td class="flex-row">
                            <input
                                name="breaks[{{ $i }}][start]"
                                class="time-input"
                                type="time"
                                value="{{ old("breaks.$i.start", $break['start']) }}"
                                {{ $canEdit ? '' : 'disabled' }}>
                            <span class="tilde">〜</span>
                            <input
                                name="breaks[{{ $i }}][end]"
                                class="time-input"
                                type="time"
                                value="{{ old("breaks.$i.end", $break['end']) }}"
                                {{ $canEdit ? '' : 'disabled' }}>
                        </td>
                    </tr>
                @empty
                    {{-- 既存が0でもこの後に空1行を追加する --}}
                @endforelse

                {{-- 常に「追加の空行」を1行出す（初回表示で休憩2が空欄になる） --}}
                @php $next = count($breaks); @endphp
                <tr>
                    <th>休憩{{ $next + 1 }}</th>
                    <td class="flex-row">
                        <input
                            name="breaks[{{ $next }}][start]"
                            class="time-input"
                            type="time"
                            value="{{ old("breaks.$next.start") }}"
                            {{ $canEdit ? '' : 'disabled' }}>
                        <span class="tilde">〜</span>
                        <input
                            name="breaks[{{ $next }}][end]"
                            class="time-input"
                            type="time"
                            value="{{ old("breaks.$next.end") }}"
                            {{ $canEdit ? '' : 'disabled' }}>
                    </td>
                </tr>

                <tr>
                    <th>備考</th>
                    <td>
                        <textarea
                            name="remarks"
                            rows="3"
                            class="remarks-input"
                            {{ $canEdit ? '' : 'disabled' }}
                        >{{ old('remarks', $detail->remarks) }}</textarea>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="btn-area">
            @if($isAdmin)
                {{-- 管理者：直接修正（レコードあり→update / なし→upsert） --}}
                @if($hasAttendance)
                    <button
                        formaction="{{ route('admin.attendance.update', ['id' => $detail->id]) }}"
                        formmethod="POST"
                        class="btn btn-secondary"
                    >修正</button>
                @else
                    <button
                        formaction="{{ route('admin.attendance.upsert') }}"
                        formmethod="POST"
                        class="btn btn-secondary"
                    >修正</button>
                @endif

                {{-- 管理者：申請がある場合のみ承認ボタン --}}
                @if($pending && ($pending->status ?? 'pending') === 'pending')
                    <button type="submit" class="btn btn-primary">承認</button>
                @endif
            @else
                {{-- 一般ユーザー --}}
                @if(!$hasAttendance)
                    <span class="text-muted">この日の勤怠レコードがありません。打刻後に修正申請できます。</span>
                @elseif($hasPending)
                    <span class="text-muted">承認待ちのため修正はできません。</span>
                @else
                    <button type="submit" class="btn-update">修正申請を送信</button>
                @endif
            @endif
        </div>
    </form>
</div>
@endsection
