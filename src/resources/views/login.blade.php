{{-- resources/views/login.blade.php --}}
@extends('layouts.app')

@section('title', 'ログイン')

@section('content')
<form action="{{ route('login') }}" method="POST">
  @csrf
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
  <button type="submit">ログイン</button>
  <p><a href="{{ route('register') }}">会員登録はこちら</a></p>
</form>
@endsection