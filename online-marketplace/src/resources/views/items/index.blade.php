@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/items.css">
@endpush


@section('content')
<div class="items-page">
    <div class="items-page__tabs">
        <a href="{{ $keyword ? '/?keyword=' . $keyword : '/' }}" class="items-page__tab {{ request('tab') !== 'mylist' ? 'is-active' : '' }}">
            おすすめ
        </a>
        <a href="{{ $keyword ? '/?tab=mylist&keyword=' . $keyword : '/?tab=mylist' }}" class="items-page__tab {{ request('tab') === 'mylist' ? 'is-active' : '' }}">
            マイリスト
        </a>
    </div>

    <div class="items-list">
        @foreach($items as $item)
        @if($item->transaction)
        <div class="item-card item-card--sold">
            @else
                <a href="/item/{{ $item->id }}" class="item-card">
            @endif
            <div class="item-card__image">
                @if($item->image)
                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">
                @else
                    <span>商品画像</span>
                @endif
                @if($item->transaction)
                    <div class="item-card__sold">SOLD</div>
                @endif
            </div>

            <div class="item-card__body">
                <p class="item-card__name">{{ $item->name }}</p>
                <p class="item-card__price">¥{{ number_format($item->price) }}</p>
                <p class="item-card__brand">{{ $item->brand ?? 'ブランド名' }}</p>
            </div>
            @if($item->transaction)
        </div>
        @else
            </a>
        @endif
        @endforeach
    </div>
</div>
@endsection
