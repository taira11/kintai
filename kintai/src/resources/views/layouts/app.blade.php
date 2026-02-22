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

<header class="header">
  <div class="header__inner">

    <a href="/attendance" class="brand">
      COACHTECH
    </a>

    <nav class="nav">
      <a href="/attendance">勤怠</a>
      <a href="/attendance/list">勤怠一覧</a>
      <a href="/stamp_correction_request/list">申請</a>

      <form method="POST" action="/logout">
        @csrf
        <button type="submit">ログアウト</button>
      </form>
    </nav>

  </div>
</header>

<main class="container">
  @yield('content')
</main>

</body>
</html>
