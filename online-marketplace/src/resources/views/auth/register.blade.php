@extends('layouts.auth')

@push('styles')
<link rel="stylesheet" href="/css/auth.css">
@endpush

@section('content')
<div class="auth-container">
    <h2 class="auth-title">会員登録</h2>
    <form method="POST" action="/register" class="auth-form" novalidate>
        @csrf
        <div class="auth-form-group">
            <label for="name">ユーザー名</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}">
            @error('name')
                <p class="auth-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form-group">
            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}">
            @error('email')
                <p class="auth-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form-group">
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password">
            @error('password')
                <p class="auth-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form-group">
            <label for="password_confirmation">確認用パスワード</label>
            <input type="password" id="password_confirmation" name="password_confirmation">
            @error('password_confirmation')
                <p class="auth-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="auth-submit-btn">登録する</button>

        <div class="auth-link">
            <a href="/login">ログインはこちら</a>
        </div>
    </form>
</div>
@endsection
