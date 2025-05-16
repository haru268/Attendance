{{-- resources/views/admin_staff_list.blade.php --}}
@extends('layouts.app')

@section('title', 'スタッフ一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin_staff_list.css') }}">
@endpush

@section('content')
<div class="adminstaff-container">

  {{-- ヘッダー（黒い縦棒＋タイトル） --}}
  <div class="adminstaff-header">
    <span class="adminstaff-bar"></span>
    <h2>スタッフ一覧</h2>
  </div>

  {{-- 白背景カード --}}
  <div class="adminstaff-card">
    <table class="adminstaff-table">
      <thead>
        <tr>
          <th>名前</th>
          <th>メールアドレス</th>
          <th>月次勤怠</th>
        </tr>
      </thead>
      <tbody>
        @forelse($staff as $member)
          <tr>
            <td>{{ $member->name }}</td>
            <td>{{ $member->email }}</td>
            <td>
              <a href="{{ route('admin.attendance.staff', ['id' => $member->id]) }}"
                 class="adminstaff-link">
                詳細
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="3">スタッフがいません。</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

</div>
@endsection
