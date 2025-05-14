{{-- resources/views/partials/header.blade.php --}}
<header class="header">
  {{-- ───────────────── ロゴ ───────────────── --}}
  <div class="header__logo">
    <img src="{{ asset('img/logo.svg') }}" alt="勤怠管理アプリ">
  </div>

  {{-- ──────────────── ナビゲーション ──────────────── --}}
  @php
    // ログイン／会員登録／管理者ログイン画面ではメニューを非表示にしたい
    $hideNav = request()->is('login', 'register', 'admin/login');
  @endphp

  @if(Auth::check() && ! $hideNav)
    <nav class="header__nav">
      <ul class="header__nav-list">

        {{-- ▼ 管理者メニュー ─────────────────────── --}}
        @if(Auth::user()->is_admin)
          <li class="header__nav-item">
            <a href="{{ route('attendance.list') }}">勤怠一覧</a>
          </li>

          <li class="header__nav-item">
            {{-- ★ ここを admin.staff.list に修正しました ★ --}}
            <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
          </li>

          <li class="header__nav-item">
            {{-- 未承認タブを最初に開きたいので status=pending を付与 --}}
            <a href="{{ route('stamp_correction_request.list', ['status'=>'pending']) }}">
              申請一覧
            </a>
          </li>

        {{-- ▼ 一般ユーザーメニュー ───────────────── --}}
        @else
          <li class="header__nav-item">
            <a href="{{ route('attendance') }}">勤怠</a>
          </li>

          <li class="header__nav-item">
            <a href="{{ route('attendance.list') }}">勤怠一覧</a>
          </li>

          <li class="header__nav-item">
            <a href="{{ route('stamp_correction_request.list') }}">申請一覧</a>
          </li>
        @endif

        {{-- ▼ 共通：ログアウト ───────────────────── --}}
        <li class="header__nav-item">
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
          </form>
          <a href="#"
             onclick="event.preventDefault();document.getElementById('logout-form').submit();">
            ログアウト
          </a>
        </li>

      </ul>
    </nav>
  @endif
</header>
