{{-- resources/views/partials/header.blade.php --}}
<header class="header">
  {{-- ───────────────── ロゴ ───────────────── --}}
  <div class="header__logo">
    <img src="{{ asset('img/logo.svg') }}" alt="勤怠管理アプリ">
  </div>

  @php
    // ログイン／会員登録／管理者ログイン画面ではメニューを非表示に
    $hideNav = request()->is('login', 'register', 'admin/login');
    $user    = Auth::user();
  @endphp

  @if(Auth::check() && ! $hideNav)
    <nav class="header__nav">
      <ul class="header__nav-list">

        {{-- ▼ 管理者メニュー ─────────────────────── --}}
        @if($user->is_admin)
          {{-- 管理者：日次勤怠一覧 (admin.attendance.list) --}}
          <li class="header__nav-item">
            <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
          </li>
          {{-- 管理者：スタッフ一覧 --}}
          <li class="header__nav-item">
            <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
          </li>
          {{-- 管理者：申請一覧（承認待ち） --}}
          <li class="header__nav-item">
            <a href="{{ route('stamp_correction_request.list', ['status' => 'pending']) }}">申請一覧</a>
          </li>

        {{-- ▼ 一般ユーザーメニュー ───────────────── --}}
        @else
          {{-- 一般：打刻 --}}
          <li class="header__nav-item">
            <a href="{{ route('attendance') }}">勤怠</a>
          </li>
          {{-- 一般：月次勤怠一覧 --}}
          <li class="header__nav-item">
            <a href="{{ route('attendance.list') }}">勤怠一覧</a>
          </li>
          {{-- 一般：申請一覧 --}}
          <li class="header__nav-item">
            <a href="{{ route('stamp_correction_request.list', ['status' => 'pending']) }}">申請一覧</a>
          </li>
        @endif

        {{-- ▼ 共通：ログアウト ───────────────────── --}}
        <li class="header__nav-item">
          @php
            // 管理者か一般かでログアウト先を切り替え
            $logoutRoute = $user->is_admin
                           ? route('admin.logout')
                           : route('logout');
          @endphp
          <form id="logout-form" action="{{ $logoutRoute }}" method="POST" style="display: none;">
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
</header>
