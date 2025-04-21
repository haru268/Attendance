{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title', '勤怠管理アプリ')</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
  {{-- 部分テンプレートとして分けたヘッダーを読み込む --}}
  @include('partials.header')

  <main>
    @yield('content')
  </main>

  <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
