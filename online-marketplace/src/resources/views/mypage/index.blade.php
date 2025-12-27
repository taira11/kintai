@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/mypage.css">
@endpush

@section('content')

<div class="mypage-container">
    <div class="mypage-header">
        <img src="{{ Auth::user()->profile && Auth::user()->profile->profile_image ? asset('storage/' . Auth::user()->profile->profile_image) : '/images/default-icon.png' }}" class="mypage-avatar">
        <div class="mypage-user-info">
            <h2 class="mypage-username">{{ Auth::user()->profile->nickname ?? 'ユーザー名' }}</h2>
        </div>
        <a href="/mypage/edit" class="mypage-edit-btn">プロフィールを編集</a>
    </div>

    <div class="mypage-tabs">
        <a href="?tab=selling" class="mypage-tab {{ request('tab', 'selling') === 'selling' ? 'active' : '' }}">
            出品した商品
        </a>
        <a href="?tab=bought" class="mypage-tab {{ request('tab') === 'bought' ? 'active' : '' }}">
            購入した商品
        </a>
    </div>

    <div class="mypage-line"></div>

    @if ($page === 'sell')
        <div class="mypage-items">
            @forelse ($items as $item)
                <a href="/item/{{ $item->id }}" class="mypage-item-card">
                    <div class="mypage-item-image">
                        @if($item->image)
                            <img src="{{ asset('storage/'.$item->image) }}">
                        @else
                            <span>商品画像</span>
                        @endif
                    </div>
                    <p class="mypage-item-name">{{ $item->name }}</p>
                </a>
            @empty
                <p class="mypage-empty-text">出品した商品はありません</p>
            @endforelse
        </div>
    @endif

    @if ($page === 'buy')
    <div class="mypage-items">
        @forelse ($items as $item)
            <div class="mypage-item-card mypage-item-card--sold">
                <div class="mypage-item-image">
                    @if($item->image)
                        <img src="{{ asset('storage/'.$item->image) }}">
                    @else
                        <span>商品画像</span>
                    @endif
                </div>
                <p class="mypage-item-name">{{ $item->name }}</p>
            </div>

        @empty
            <p class="mypage-empty-text">購入した商品はありません</p>
        @endforelse
    </div>
    @endif
</div>
@endsection
