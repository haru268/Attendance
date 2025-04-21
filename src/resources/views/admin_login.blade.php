{{-- resources/views/admin_login.blade.php --}}
@extends('layouts.app')

@section('title', '管理者ログイン')

@section('content')
  <div class="login-container">
    <h2>管理者ログイン</h2>

    @if(session('error'))
      <div class="error" style="color: red; margin-bottom: 1rem;">
        {{ session('error') }}
      </div>
    @endif

    <form method="POST" action="{{ route('admin.login') }}">
      @csrf

      <div class="form-group">
        <label for="email">メールアドレス</label>
        <input
          type="email"
          id="email"
          name="email"
          value="{{ old('email') }}"
          required
          autofocus
        >
        @error('email')
          <div class="error" style="color: red;">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label for="password">パスワード</label>
        <input
          type="password"
          id="password"
          name="password"
          required
        >
        @error('password')
          <div class="error" style="color: red;">{{ $message }}</div>
        @enderror
      </div>

      <button type="submit">ログイン</button>
    </form>
  </div>
@endsection
