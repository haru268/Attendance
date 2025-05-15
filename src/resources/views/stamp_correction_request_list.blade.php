{{-- resources/views/stamp_correction_request_list.blade.php --}}

@php
    // クエリパラメータ status を取得（'pending' or 'approved'）
    $status = request('status', 'pending');
@endphp

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

    {{-- ▼ タブ --}}
    <nav class="stampcr-tabs">
        <a href="{{ route('stamp_correction_request.list', ['status' => 'pending']) }}"
           class="stampcr-tab {{ $status === 'pending' ? 'stampcr-tab--active' : '' }}">
            承認待ち
        </a>
        <a href="{{ route('stamp_correction_request.list', ['status' => 'approved']) }}"
           class="stampcr-tab {{ $status === 'approved' ? 'stampcr-tab--active' : '' }}">
            承認済み
        </a>
    </nav>

    {{-- ▼ テーブルカード --}}
    <div class="stampcr-card">
        <table class="stampcr-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
            @foreach($revisionRequests as $r)
                <tr>
                    {{-- 状態 --}}
                    <td>
                        <span class="{{ $r->status === 'pending'
                                       ? 'stampcr-badge--pending'
                                       : 'stampcr-badge--approved' }}">
                            {{ $r->status === 'pending' ? '承認待ち' : '承認済み' }}
                        </span>
                    </td>

                    {{-- 名前 --}}
                    <td>{{ optional($r->user)->name ?? '—' }}</td>

                    {{-- 対象日（勤怠の日付） --}}
                    <td>
                        {{ optional($r->attendance)->date
                             ? \Carbon\Carbon::parse($r->attendance->date)->format('Y/m/d')
                             : '—' }}
                    </td>

                    {{-- 申請理由 --}}
                    <td>{{ $r->reason }}</td>

                    {{-- 申請日時 --}}
                    <td>{{ optional($r->created_at)->format('Y/m/d') }}</td>

                    {{-- 詳細リンク --}}
                    <td>
                        <a class="stampcr-detail-link"
                           href="{{ route('attendance.detail', $r->attendance_id) }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
