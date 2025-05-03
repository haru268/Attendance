{{-- resources/views/login.blade.php --}}
@extends('layouts.app')

@section('title','ログイン')
@section('bodyClass','auth-page')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
<div class="login-container">
  <h1>ログイン</h1>

  <form action="{{ route('login') }}" method="POST">
    @csrf

    {{-- メールアドレス --}}
    <label>メールアドレス</label>
    <input type="email" name="email" value="{{ old('email') }}">
    @error('email') <span class="error">{{ $message }}</span> @enderror

    {{-- パスワード --}}
    <label>パスワード</label>
    <input type="password" name="password">
    @error('password') <span class="error">{{ $message }}</span> @enderror

    <button type="submit" class="btn-login">ログイン</button>
  </form>

  <p class="login-register-link">
    <a href="{{ route('register.form') }}">会員登録はこちら</a>
  </p>
</div>
@endsection
