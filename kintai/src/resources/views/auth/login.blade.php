@extends('layouts.auth')

@push('styles')
<link rel="stylesheet" href="/css/auth.css">
@endpush

@section('content')
<div class="auth-container">
  <h2 class="auth-title">ログイン</h2>

  <form method="POST" action="/login" class="auth-form" novalidate>
    @csrf

    <div class="auth-form-group">
      <label for="email">メールアドレス</label>
      <input
        type="email"
        id="email"
        name="email"
        value="{{ old('email') }}"
      >
      @error('email')
        <p class="auth-error">{{ $message }}</p>
      @enderror
    </div>

    <div class="auth-form-group">
      <label for="password">パスワード</label>
      <input
        type="password"
        id="password"
        name="password"
      >
      @error('password')
        <p class="auth-error">{{ $message }}</p>
      @enderror
    </div>

    <button type="submit" class="auth-submit-btn">
      ログインする
    </button>

    <div class="auth-link">
      <a href="/register">会員登録はこちら</a>
    </div>
  </form>
</div>
@endsection
