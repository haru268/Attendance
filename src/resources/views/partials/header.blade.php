{{-- resources/views/partials/header.blade.php --}}
<header class="header">
  <div class="header__logo">
    <img src="{{ asset('img/logo.svg') }}" alt="勤怠管理アプリ">
  </div>

  @auth
    {{-- “ログイン・登録・管理者ログイン” 画面ではナビを出さない --}}
    @if(! request()->is('login','register','admin/login'))
      <nav class="header__nav">
        <ul class="header__nav-list">

          {{-- ─────────────────────────────
               管理者メニュー
          ─────────────────────────────--}}
          @if(Auth::user()->is_admin)
            <li class="header__nav-item">
              <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
            </li>
            <li class="header__nav-item">
              <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
            </li>
            <li class="header__nav-item">
              <a href="{{ route('admin.revision.list') }}">申請一覧</a>
            </li>

          {{-- ─────────────────────────────
               一般ユーザーメニュー
          ─────────────────────────────--}}
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

          {{-- ─────────────────────────────
               共通：ログアウト（POST）
          ─────────────────────────────--}}
          <li class="header__nav-item">
            @php
              $logoutRoute = Auth::user()->is_admin ? 'admin.logout' : 'logout';
            @endphp
            <form id="logout-form" action="{{ route($logoutRoute) }}" method="POST" style="display:none;">
              @csrf
            </form>
            <a href="#"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              ログアウト
            </a>
          </li>
        </ul>
      </nav>
    @endif
  @endauth
</header>
