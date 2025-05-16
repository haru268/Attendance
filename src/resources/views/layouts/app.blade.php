<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title', '勤怠管理アプリ')</title>

  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">

  <!-- 共通の各画面用CSS -->
  <link rel="stylesheet" href="{{ asset('css/register.css') }}">
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
  <link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
  <link rel="stylesheet" href="{{ asset('css/stamp_correction_request.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin_login.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
  
  <!-- ここでスタッフ一覧用CSSを読み込み -->
  <link rel="stylesheet" href="{{ asset('css/admin_staff_list.css') }}">

  <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">

  @stack('styles')
</head>
<body>
  @include('partials.header')
  <main class="container">
    @yield('content')
  </main>
  @stack('scripts')
</body>
</html>
