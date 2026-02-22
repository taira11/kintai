@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-login.css') }}">
@endpush

@section('content')
<div class="admin-login">
  <h1 class="admin-login__title">管理者ログイン</h1>

  <form method="POST" action="{{ route('admin.login.submit') }}" novalidate>
    @csrf

    <div class="form-group">
      <label for="email">メールアドレス</label>
      <input
        id="email"
        type="email"
        name="email"
        value="{{ old('email') }}"
      >
      @error('email')
        <p class="error">{{ $message }}</p>
      @enderror
    </div>

    <div class="form-group">
      <label for="password">パスワード</label>
      <input
        id="password"
        type="password"
        name="password"
      >
      @error('password')
        <p class="error">{{ $message }}</p>
      @enderror
    </div>

    <button class="btn" type="submit">管理者ログインする</button>
  </form>
</div>
@endsection
