{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title', '勤怠管理アプリ')</title>


   <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <link rel="stylesheet" href="{{ asset('css/register.css') }}">
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
  <link rel="stylesheet" href="{{ asset('cssattendance.css') }}">
  <link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
  <link rel="stylesheet" href="{{ asset('css/stamp_correction_request.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin_login.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@stack('styles')


</head>
<body class="@yield('bodyClass')">
  @include('partials.header')

  {{-- ここから追加 --}}
  <main class="container">
    @yield('content')
  </main>
  {{-- ここまで追加 --}}

  <script src="{{ asset('js/app.js') }}"></script>
  @stack('scripts')
</body>
</html>