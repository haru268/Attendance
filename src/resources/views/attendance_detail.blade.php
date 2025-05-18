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
    use Carbon\Carbon;

    $dt       = Carbon::parse($detail->date);
    $isDummy  = is_null($detail->id);
    $isAdmin  = Auth::user()->is_admin;

    /* ダミー固定値 */
    $dummyIn  = '09:00';  $dummyOut = '18:00';
    $dummyBrk = [['start'=>'12:00','end'=>'13:00']];

    /* ブレーク行を必ず１行以上用意 */
    $breaks = $isDummy ? $dummyBrk : ($detail->breaks ?? []);
    if(!$isDummy && count($breaks)===0){ $breaks[]=['start'=>'','end'=>'']; }

    /* 承認待ち申請 */
    $pending   = $pendingRequest ?? null;

    // 時刻フォーマット
    if ($isDummy) {
        $rawIn  = $dummyIn;
        $rawOut = $dummyOut;
    } elseif ($pending) {
        $rawIn  = $pending->proposed_clock_in;
        $rawOut = $pending->proposed_clock_out;
    } else {
        $rawIn  = $detail->clock_in;
        $rawOut = $detail->clock_out;
    }
    $in  = Carbon::parse($rawIn)->format('H:i');
    $out = Carbon::parse($rawOut)->format('H:i');
@endphp

<div class="detail-container">
    <div class="detail-header"><h2>勤怠詳細</h2></div>

    {{-- バリデーションエラー --}}
    @if($errors->any())
        <div style="margin-bottom:1rem;">
            @foreach(array_unique($errors->all()) as $msg)
                <p style="color:red;margin:.25rem 0;">{{ $msg }}</p>
            @endforeach
        </div>
    @endif

    {{-- ダミー注意 --}}
    @if($isDummy)
        <p style="color:#666">この勤怠はダミーデータのため、編集できません。</p>
    @endif

    <form
        @if(!$isDummy && !$isAdmin && !$hasPending) action="{{ route('attendance.update',$detail->id) }}" method="POST"
        @else action="#" @endif>

        @csrf @if(!$isDummy && !$isAdmin && !$hasPending) @method('PATCH') @endif

        <table class="detail-table"><tbody>
            <tr><th>名前</th><td>{{ $detail->user->name }}</td></tr>
            <tr><th>日付</th><td>{{ $dt->format('Y年 n月 j日') }}</td></tr>

            {{-- 出勤・退勤 --}}
            <tr>
                <th>出勤・退勤</th>
                <td class="flex-row">
                    <input name="clock_in"  class="time-input" type="text" value="{{ old('clock_in',$in) }}"
                           {{ ($isDummy||$hasPending||$isAdmin)?'disabled':'' }}>
                    <span class="tilde">〜</span>
                    <input name="clock_out" class="time-input" type="text" value="{{ old('clock_out',$out) }}"
                           {{ ($isDummy||$hasPending||$isAdmin)?'disabled':'' }}>
                </td>
            </tr>

            {{-- 休憩 --}}
            @foreach($breaks as $i=>$bk)
                @php
                    $st = $pending->breaks[$i]['start'] ?? $bk['start'] ?? '';
                    $ed = $pending->breaks[$i]['end']   ?? $bk['end']   ?? '';
                    $start = $st ? Carbon::parse($st)->format('H:i') : '';
                    $end   = $ed ? Carbon::parse($ed)->format('H:i') : '';
                @endphp
                <tr>
                    <th>休憩{{ $i+1 }}</th>
                    <td class="flex-row">
                        <input name="breaks[{{ $i }}][start]" class="time-input" type="text" value="{{ old("breaks.$i.start",$start) }}"
                               {{ ($isDummy||$hasPending||$isAdmin)?'disabled':'' }}>
                        <span class="tilde">〜</span>
                        <input name="breaks[{{ $i }}][end]"   class="time-input" type="text" value="{{ old("breaks.$i.end",$end) }}"
                               {{ ($isDummy||$hasPending||$isAdmin)?'disabled':'' }}>
                    </td>
                </tr>
            @endforeach

            <tr><th>備考</th>
                <td>
                    <textarea name="remarks" rows="3" class="remarks-input" {{ ($isDummy||$hasPending||$isAdmin)?'disabled':'' }}>{{ old('remarks',$pending->proposed_remarks ?? $detail->remarks) }}</textarea>
                </td>
            </tr>
        </tbody></table>

        {{-- ▼ボタン／ラベル --}}
        <div class="btn-area">
            {{-- 一般ユーザー --}}
            @if(!$isAdmin)
                @if(!$isDummy && !$hasPending)
                    <button class="btn-update">修正</button>
                @elseif($hasPending)
                    <p style="color:red;">※承認待ちのため修正はできません。</p>
                @endif
            {{-- 管理者 --}}
            @else
                @if($isDummy)
                    <p style="color:#666;">ダミーデータのため修正できません。</p>
                @elseif($hasPending)
                    <form style="display:inline" action="{{ route('admin.revision.approve',$pending->id) }}" method="POST">
                        @csrf
                        <button class="btn-approve">承認</button>
                    </form>
                @else
                    <button class="btn-update">修正</button>
                @endif
            @endif
        </div>
    </form>
</div>
@endsection
```
