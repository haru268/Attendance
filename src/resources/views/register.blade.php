{{-- resources/views/register.blade.php --}}
@extends('layouts.app')

@section('title','会員登録')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endpush

@section('content')
<div class="register-container">
  <h1>会員登録</h1>

  {{-- 全フィールド横断のエラーをまとめて表示したい場合はここに --}}
  @if ($errors->any())
    <ul class="error-list">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  @endif

  <form action="{{ route('register') }}" method="POST">
    @csrf

    {{-- 名前 --}}
    <label>お名前</label>
    <input type="text" name="name" value="{{ old('name') }}">
    @error('name') <span class="error">{{ $message }}</span> @enderror

    {{-- メールアドレス --}}
    <label>メールアドレス</label>
    <input type="email" name="email" value="{{ old('email') }}">
    @error('email') <span class="error">{{ $message }}</span> @enderror

    {{-- パスワード --}}
    <label>パスワード</label>
    <input type="password" name="password">
    @error('password') <span class="error">{{ $message }}</span> @enderror

    {{-- パスワード（確認） --}}
    <label>パスワード（確認）</label>
    <input type="password" name="password_confirmation">
    @error('password_confirmation') <span class="error">{{ $message }}</span> @enderror

    <button type="submit" class="btn-primary">登録</button>
  </form>

  <p class="link-to-login">
    <a href="{{ route('login.form') }}">ログインはこちら</a>
  </p>
</div>
@endsection
