@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/item-detail.css">
@endpush

@section('content')
<div class="detail-wrapper">
    <div class="detail-left">
        @if($item->image)
            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="detail-image">
        @else
            <div class="detail-image-placeholder">商品画像</div>
        @endif
    </div>

    <div class="detail-right">
        <h2 class="detail-title">{{ $item->name }}</h2>
        <p class="detail-brand">{{ $item->brand }}</p>
        <p class="detail-price">¥{{ number_format($item->price) }} <span class="detail-tax">(税込)</span> </p>
        <div class="detail-icons">
            <button type="button" class="favorite-btn" data-id="{{ $item->id }}" style="background:none;border:none;cursor:pointer;padding:0;">
                <img src="{{ $item->is_favorited ? '/images/heart-pink.png' : '/images/heart-gray.png' }}" class="detail-icon heart-icon" data-default="/images/heart-gray.png" data-active="/images/heart-pink.png">
                <span class="detail-icon-num">{{ $item->favorites_count }}</span>
            </button>

            <div class="detail-icon-block">
                <img src="/images/comment.png" class="detail-icon">
                <span class="detail-icon-num">{{ $comments->count() }}</span>
            </div>
        </div>

        @if(Auth::id() === $item->seller_id)
        <div class="detail-buy-btn detail-buy-btn--disabled">
            自分が出品した商品は購入できません
        </div>
        @elseif($item->status === \App\Models\Product::STATUS_SOLD)
        <div class="detail-buy-btn detail-buy-btn--disabled">
            売り切れました
        </div>
        @else
        <a href="/purchase/{{ $item->id }}" class="detail-buy-btn">
            購入手続きへ
        </a>
        @endif

        <h3 class="detail-section-title">商品説明</h3>
        <p class="detail-text">{{ $item->description }}</p>
        <h3 class="detail-section-title">商品の情報</h3>
        <div class="detail-info-row">
            <div class="info-label">カテゴリー</div>
            <div class="info-value">
                @foreach($item->categories as $cat)
                    <span class="info-tag">{{ $cat->name }}</span>
                @endforeach
            </div>
        </div>

        <div class="detail-info-row">
            <div class="info-label">商品の状態</div>
            <div class="info-value">{{ $item->status_label }}</div>
        </div>

        <h3 class="detail-section-title">コメント（{{ $comments->count() }}）</h3>

        @foreach($comments as $comment)
        <div class="comment-box">
            <div class="comment-header">
                <div class="comment-icon">
                    <img src="{{ $comment->user->profile && $comment->user->profile->profile_image ? asset('storage/' . $comment->user->profile->profile_image) : '/images/default-icon.png' }}" alt="user icon">
                </div>
                <p class="comment-user">{{ $comment->user->profile->nickname }}</p>
            </div>

            <p class="comment-content">
                {{ $comment->comment }}
            </p>
        </div>
        @endforeach
        @auth
        <h3 class="detail-section-title">商品へのコメント</h3>
        <form method="POST" action="/item/{{ $item->id }}/comment">
            @csrf
            <textarea name="content" class="detail-textarea" placeholder="コメントを入力"></textarea>

            @error('content')
                <p class="error-text">{{ $message }}</p>
            @enderror

            <button class="detail-comment-btn">コメントを送信する</button>
        </form>
        @endauth
    </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', () => {

    const btn = document.querySelector('.favorite-btn');
    if (!btn) return;

    const img = btn.querySelector('.heart-icon');
    const countTag = btn.querySelector('.detail-icon-num');
    const itemId = btn.dataset.id;

    btn.addEventListener('click', () => {

        fetch(`/item/${itemId}/favorite`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Network error');
            return res.json();
        })
        .then(data => {

            img.src = data.favorited
                ? img.dataset.active
                : img.dataset.default;

            countTag.textContent = data.count;
        })
        .catch(err => {
            console.error('favorite error:', err);
        });
    });
});
</script>
@endsection
