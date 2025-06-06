{{-- resources/views/stamp_correction_request_list.blade.php --}}
@extends('layouts.app')

@section('title','申請一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request.css') }}">
@endpush

@section('content')
@php($isPending = $status === 'pending')
<div class="stampcr-container">
  <div class="stampcr-header">
      <span class="stampcr-bar"></span><h2>申請一覧</h2>
  </div>

  {{-- ▼ タブ --}}
  <nav class="stampcr-tabs">
      <a href="{{ route('stamp_correction_request.list',['status'=>'pending']) }}"
         class="stampcr-tab {{ $isPending ? 'stampcr-tab--active' : '' }}">
         承認待ち
      </a>
      <a href="{{ route('stamp_correction_request.list',['status'=>'approved']) }}"
         class="stampcr-tab {{ $isPending ? '' : 'stampcr-tab--active' }}">
         承認済み
      </a>
  </nav>

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
                  @if($isAdmin)<th>操作</th>@endif
              </tr>
          </thead>
          <tbody>
          @forelse($revisionRequests as $r)
              <tr>
                  {{-- 状態 --}}
                  <td>
                      <span class="{{ $r->status==='pending' ? 'stampcr-badge--pending' : 'stampcr-badge--approved' }}">
                          {{ $r->status==='pending' ? '承認待ち' : '承認済み' }}
                      </span>
                  </td>

                  {{-- 名前 --}}
                  <td>{{ $r->user->name }}</td>

                  {{-- 対象日 --}}
                  <td>{{ \Carbon\Carbon::parse($r->attendance->date)->format('Y/m/d') }}</td>

                  {{-- 申請理由 --}}
                  <td>{{ $r->proposed_remarks }}</td>

                  {{-- 申請日時 --}}
                  <td>{{ $r->created_at->format('Y/m/d') }}</td>

                  {{-- 詳細リンク --}}
                  <td>
                      <a href="{{ route('attendance.detail', ['key' => $r->attendance->id]) }}">詳細</a>
                  </td>

                  {{-- 操作列（管理者のみ） --}}
                  @if($isAdmin)
                      <td>
                          @if($r->status==='pending')
                              <form method="POST"
                                    action="{{ route('admin.revision.approve',$r->id) }}"
                                    onsubmit="return confirm('承認しますか？');">
                                  @csrf
                                  <button class="btn-approve">承認</button>
                              </form>
                          @else
                              <span style="color:#888;">—</span>
                          @endif
                      </td>
                  @endif
              </tr>
          @empty
              <tr>
                  <td colspan="{{ $isAdmin ? 7 : 6 }}" class="text-center">データがありません</td>
              </tr>
          @endforelse
          </tbody>
      </table>
  </div>
</div>
@endsection
