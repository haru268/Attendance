{{-- resources/views/register.blade.php --}}
@extends('layouts.app')

@section('title','会員登録')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endpush

@section('content')
<div class="register-container">
  <h1>会員登録</h1>

  {{-- 全フィールド横断のエラー --}}
  @if ($errors->any())
    <ul class="error-list">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  @endif

  <form action="{{ route('register') }}" method="POST">
    @csrf

    <label for="name">名前</label>
    <input id="name" type="text" name="name" value="{{ old('name') }}">
    @error('name') <span class="error">{{ $message }}</span> @enderror

    <label for="email">メールアドレス</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}">
    @error('email') <span class="error">{{ $message }}</span> @enderror

    <label for="password">パスワード</label>
    <input id="password" type="password" name="password">
    @error('password') <span class="error">{{ $message }}</span> @enderror

    <label for="password_confirmation">パスワード確認</label>
    <input id="password_confirmation" type="password" name="password_confirmation">
    @error('password_confirmation') <span class="error">{{ $message }}</span> @enderror

    <button type="submit" class="btn-submit">登録する</button>
  </form>

  <p class="link-to-login">
    <a href="{{ route('login.form') }}">ログインはこちら</a>
  </p>
</div>
@endsection
