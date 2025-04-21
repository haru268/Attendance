{{-- resources/views/register.blade.php --}}
@extends('layouts.app')

@section('title', '会員登録')

@section('content')
<form action="{{ route('register') }}" method="POST">
  @csrf
  <div>
    <label>お名前</label>
    <input type="text" name="name" value="{{ old('name') }}">
    @error('name')<p class="error">{{ $message }}</p>@enderror
  </div>
  <div>
    <label>メールアドレス</label>
    <input type="email" name="email" value="{{ old('email') }}">
    @error('email')<p class="error">{{ $message }}</p>@enderror
  </div>
  <div>
    <label>パスワード</label>
    <input type="password" name="password">
    @error('password')<p class="error">{{ $message }}</p>@enderror
  </div>
  <div>
    <label>パスワード（確認）</label>
    <input type="password" name="password_confirmation">
  </div>
  <button type="submit">登録</button>
  <p><a href="{{ route('login') }}">ログインはこちら</a></p>
</form>
@endsection