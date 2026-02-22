<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>COACHTECH 勤怠</title>

  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">

  @stack('styles')
</head>
<body>

@php
  $isAdminLoginPage =
      request()->routeIs('admin.login') ||
      request()->routeIs('admin.login.*');
@endphp

<header class="header">
  <div class="header__inner">

    @if($isAdminLoginPage)
      <span class="brand">COACHTECH</span>
    @else
      <a href="{{ route('admin.attendance.list') }}" class="brand">
        COACHTECH
      </a>
    @endif

    @unless($isAdminLoginPage)
      <nav class="nav">
        <a href="{{ route('admin.attendance.list') }}">
          勤怠一覧
        </a>

        <a href="{{ route('admin.staff.list') }}">
          スタッフ一覧
        </a>

        <a href="{{ route('admin.correction.list') }}">
          申請一覧
        </a>

        <form method="POST" action="{{ route('admin.logout') }}">
          @csrf
          <button type="submit">ログアウト</button>
        </form>
      </nav>
    @endunless

  </div>
</header>

<main class="container">
  @yield('content')
</main>

</body>
</html>
