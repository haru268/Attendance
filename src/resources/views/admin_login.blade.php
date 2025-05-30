{{-- resources/views/admin_login.blade.php --}}
@extends('layouts.app')

@section('title', '管理者ログイン')

{{-- 管理者ログイン画面専用の body クラス --}}
@section('bodyClass', 'adminlogin-page')

@push('styles')
    <!-- 管理者ログイン：ページ全体を白背景に強制 -->
    <style>
      html,
      body,
      main.container,
      .container,
      #app,
      .min-h-screen,
      .bg-gray-100 {
        background-color: #fff !important;
      }
    </style>
    <link rel="stylesheet" href="{{ asset('css/admin_login.css') }}">
@endpush

@section('content')
<div class="adminlogin-container">
    <h1>管理者ログイン</h1>

    {{-- 認証失敗メッセージ --}}
    @if (session('error'))
        <p class="adminlogin-error">{{ session('error') }}</p>
    @endif

    <form action="{{ route('admin.login') }}" method="POST">
        @csrf

        {{-- メールアドレス --}}
        <div class="adminlogin-form-group">
            <label for="email">メールアドレス</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')
                <p class="adminlogin-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- パスワード --}}
        <div class="adminlogin-form-group">
            <label for="password">パスワード</label>
            <input id="password" type="password" name="password" required>
            @error('password')
                <p class="adminlogin-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="adminlogin-btn-login">管理者ログインする</button>
    </form>
</div>
@endsection
