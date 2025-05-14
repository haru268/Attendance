@php
    // ?status=pending / approved
    $status = request('status','pending');
@endphp
@php($bodyClass = 'stampcr-body')   {{-- このページだけ灰色背景 --}}

@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request.css') }}">
@endpush

@section('content')
<div class="stampcr-container">

    {{-- ▲ ヘッダー：黒縦棒＋タイトル --}}
    <div class="stampcr-header">
        <span class="stampcr-bar"></span>
        <h2 class="stampcr-title">申請一覧</h2>
    </div>

    {{-- ▼ タブ（下線なし） --}}
    <nav class="stampcr-tabs">
        <a href="{{ route('stamp_correction_request.list',['status'=>'pending']) }}"
           class="stampcr-tab {{ $status==='pending' ? 'stampcr-tab--active' : '' }}">承認待ち</a>
        <a href="{{ route('stamp_correction_request.list',['status'=>'approved']) }}"
           class="stampcr-tab {{ $status==='approved' ? 'stampcr-tab--active' : '' }}">承認済み</a>
    </nav>

    {{-- ▼ 白背景カード＋テーブル --}}
    <div class="stampcr-card">
        <table class="stampcr-table">
            <thead>
                <tr>
                    <th>状態</th><th>名前</th><th>対象日時</th>
                    <th>申請理由</th><th>申請日時</th><th>詳細</th>
                </tr>
            </thead>
            <tbody>
            @foreach($revisionRequests as $r)
                <tr>
                    <td>
                        <span class="{{ $r->status==='承認待ち'
                                     ? 'stampcr-badge--pending'
                                     : 'stampcr-badge--approved' }}">
                            {{ $r->status }}
                        </span>
                    </td>
                    <td>{{ $r->user->name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d',substr($r->targetDatetime,0,10))->format('Y/m/d') }}</td>
                    <td>{{ $r->reason }}</td>
                    <td>{{ $r->created_at->format('Y/m/d') }}</td>
                    <td><a class="stampcr-detail-link" href="{{ route('attendance.detail',$r->id) }}">詳細</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
