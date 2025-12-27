<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="/css/app.css">
    @stack('styles')
</head>

<body>
    <header class="auth-header">
        <a href="/">
            <img src="/images/coachtech-logo.png" alt="COACHTECH" class="logo">
        </a>
    </header>
    <main class="auth-main">
        @yield('content')
    </main>
</body>
</html>
