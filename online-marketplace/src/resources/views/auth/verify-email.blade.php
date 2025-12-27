@extends('layouts.auth')

@push('styles')
<link rel="stylesheet" href="/css/auth.css">
@endpush

@section('content')
<div class="verify-container">
    <p class="verify-text">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>

    <a href="https://mailtrap.io/inboxes/" class="verify-btn">
        認証はこちらから
    </a>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="verify-resend">認証メールを再送する</button>
    </form>
</div>
@endsection
