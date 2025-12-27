<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH フリマ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <a href="/">
                <img src="/images/coachtech-logo.png" alt="COACHTECH" class="logo">
            </a>

            <form action="{{ url('/') }}" method="GET" class="search-form">
                <input type="text" name="keyword" placeholder="なにをお探しですか？" value="{{ request('keyword') }}">
            </form>

            <nav class="header-nav">
                @auth
                <a href="/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    ログアウト
                </a>
                <form id="logout-form" action="/logout" method="POST" class="d-none">
                    @csrf
                </form>
                <a href="/mypage">マイページ</a>
                <a href="/sell" class="sell-btn">出品</a>
                @endauth
                @guest
                    <a href="/login">ログイン</a>
                    <a href="/register">会員登録</a>
                @endguest
            </nav>
        </div>
    </header>

    <main class="container">
        @yield('content')
    </main>
</body>
</html>
