{{-- resources/views/register.blade.php --}}
@extends('layouts.app')

@section('title', '会員登録')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endpush

@section('content')
<div class="register-container">
  {{-- タイトルを追加 --}}
  <h1>会員登録</h1>

  {{-- エラーメッセージ --}}
  @if ($errors->any())
    <div class="error-messages" style="color:red; margin-bottom:1rem;">
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('register') }}">
    @csrf

    <label for="name">名前</label>
    <input type="text" id="name" name="name" value="{{ old('name') }}">

    <label for="email">メールアドレス</label>
    <input type="email" id="email" name="email" value="{{ old('email') }}">

    <label for="password">パスワード</label>
    <input type="password" id="password" name="password">

    <label for="password_confirmation">パスワード確認</label>
    <input type="password" id="password_confirmation" name="password_confirmation">

    <button type="submit" class="btn-submit">登録する</button>

    <a href="{{ route('login.form') }}" class="btn-login">ログインする</a>
  </form>
</div>
@endsection
