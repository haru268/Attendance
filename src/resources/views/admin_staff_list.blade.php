{{-- 管理者：スタッフ一覧 --}}
@extends('layouts.app')

@section('title', 'スタッフ一覧（管理者用）')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin_staff_list.css') }}">
@endpush

@section('content')
<div class="adminstaff-container">

    {{-- ▲ 見出し --}}
    <div class="adminstaff-header">
        <span class="adminstaff-bar"></span>
        <h2>スタッフ一覧</h2>
    </div>

    {{-- ▼ 一覧テーブル（ID 列を削除） --}}
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
            @foreach ($staff as $member)
                <tr>
                    <td>{{ $member->name }}</td>
                    <td>{{ $member->email }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.staff', $member->id) }}"
                           class="adminstaff-link">詳細</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
