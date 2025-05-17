{{-- resources/views/attendance_detail.blade.php ----------------------------------}}
@extends('layouts.app')

@section('title','勤怠詳細')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endpush


@section('content')
@php
    /** ----------------------------------------------------
     *  0. 変数整形（Controller から $detail, $hasPending を受取）
     * --------------------------------------------------- */
    use Illuminate\Support\Facades\Auth;

    $dt       = \Carbon\Carbon::parse($detail->date);
    $isDummy  = is_null($detail->id);
    $isAdmin  = Auth::user()->is_admin;

    /* ダミー固定値 */
    $dummyIn  = '09:00';  $dummyOut = '18:00';
    $dummyBrk = [['start'=>'12:00','end'=>'13:00']];

    /* ブレーク行を必ず１行以上用意 */
    $breaks = $isDummy ? $dummyBrk : ($detail->breaks ?? []);
    if(!$isDummy && count($breaks)===0){ $breaks[]=['start'=>'','end'=>'']; }

    /* 承認待ち申請の中身（コントローラで NULL なら false）*/
    $pending   = $pendingRequest ?? null;
@endphp

<div class="detail-container">
    <div class="detail-header"><h2>勤怠詳細</h2></div>

    {{-- ◎バリデーション一括 --}}
    @if($errors->any())
        <div style="margin-bottom:1rem;">
            @foreach(array_unique($errors->all()) as $msg)
                <p style="color:red;margin:.25rem 0;">{{ $msg }}</p>
            @endforeach
        </div>
    @endif

    {{-- ◎ダミー注意 --}}
    @if($isDummy)
        <p style="color:#666">この勤怠はダミーデータのため、編集できません。</p>
    @endif

    <form
        @if(!$isDummy && !$isAdmin && !$hasPending) action="{{ route('attendance.update',$detail->id) }}" method="POST"
        @else action="#" @endif>

        @csrf @if(!$isDummy && !$isAdmin && !$hasPending) @method('PATCH') @endif

        <table class="detail-table"><tbody>
            {{-- 名前・日付 --}}
            <tr><th>名前</th><td>{{ $detail->user->name }}</td></tr>
            <tr><th>日付</th><td>{{ $dt->format('Y年 n月 j日') }}</td></tr>

            {{-- 出勤・退勤 --}}
            <tr>
                <th>出勤・退勤</th>
                <td class="flex-row">
                    @php
                        $in  = $pending->proposed_clock_in  ?? ($isDummy? $dummyIn  : optional($detail->clock_in )->format('H:i'));
                        $out = $pending->proposed_clock_out ?? ($isDummy? $dummyOut : optional($detail->clock_out)->format('H:i'));
                    @endphp
                    <input name="clock_in"  class="time-input" type="text" value="{{ $in  ?? '' }}"
                           {{ ($isDummy||$hasPending||$isAdmin)?'disabled':'' }}>
                    <span class="tilde">〜</span>
                    <input name="clock_out" class="time-input" type="text" value="{{ $out ?? '' }}"
                           {{ ($isDummy||$hasPending||$isAdmin)?'disabled':'' }}>
                </td>
            </tr>

            {{-- 休憩 --}}
            @foreach($breaks as $i=>$bk)
                @php
                    $st = $pending->breaks[$i]['start'] ?? $bk['start'] ?? '';
                    $ed = $pending->breaks[$i]['end']   ?? $bk['end']   ?? '';
                @endphp
                <tr>
                    <th>休憩{{ $i+1 }}</th>
                    <td class="flex-row">
                        <input name="breaks[{{ $i }}][start]" class="time-input" type="text" value="{{ $st }}"
                               {{ ($isDummy||$hasPending||$isAdmin)?'disabled':'' }}>
                        <span class="tilde">〜</span>
                        <input name="breaks[{{ $i }}][end]"   class="time-input" type="text" value="{{ $ed }}"
                               {{ ($isDummy||$hasPending||$isAdmin)?'disabled':'' }}>
                    </td>
                </tr>
            @endforeach

            {{-- 備考 --}}
            <tr>
                <th>備考</th>
                <td>
                    <textarea name="remarks" rows="3" class="remarks-input"
                              {{ ($isDummy||$hasPending||$isAdmin)?'disabled':'' }}>{{ $pending->proposed_remarks ?? $detail->remarks }}</textarea>
                </td>
            </tr>
        </tbody></table>

        {{-- ▼ボタン／ラベル --}}
        <div style="margin-top:.75rem">
            {{-- 一般ユーザー用 --}}
            @if(!$isAdmin)
                @if(!$isDummy && !$hasPending)
                    <button class="btn-update">修正</button>
                @elseif($hasPending)
                    <p style="color:red;">※承認待ちのため修正はできません。</p>
                @endif
            {{-- 管理者用 --}}
            @else
                @if($hasPending)
                    <form style="display:inline" action="{{ route('admin.revision.approve',$pending->id) }}" method="POST">
                        @csrf <button class="btn-approve">承認</button>
                    </form>
                @else
                    <span style="color:green;font-weight:bold;">承認済み</span>
                @endif
            @endif
        </div>
    </form>
</div>
@endsection
