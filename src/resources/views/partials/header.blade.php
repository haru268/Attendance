{{-- resources/views/partials/header.blade.php --}}
<header class="header">
  <div class="header__logo">
    <img src="{{ asset('img/logo.svg') }}" alt="勤怠管理アプリ">
  </div>

  @if(Auth::check() && !request()->is('login','register','admin/login'))
    @if(Auth::user()->is_admin)
      <nav class="header__nav">
        <ul class="header__nav-list">
          <li class="header__nav-item">
            <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
          </li>
          <li class="header__nav-item">
            <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
          </li>
          <li class="header__nav-item">
            {{-- ここを修正：admin.revision.list に飛ばす --}}
            <a href="{{ route('admin.revision.list') }}">申請一覧</a>
          </li>
          <li class="header__nav-item">
            <a href="{{ route('admin.logout') }}">ログアウト</a>
          </li>
        </ul>
      </nav>
    @else
      <nav class="header__nav">
        <ul class="header__nav-list">
          <li class="header__nav-item">
            <a href="{{ route('attendance') }}">勤怠</a>
          </li>
          <li class="header__nav-item">
            <a href="{{ route('attendance.list') }}">勤怠一覧</a>
          </li>
          <li class="header__nav-item">
            <a href="{{ route('admin.revision.list') }}">申請</a>
          </li>
          <li class="header__nav-item">
            <a href="{{ route('logout') }}">ログアウト</a>
          </li>
        </ul>
      </nav>
    @endif
  @endif
</header>
