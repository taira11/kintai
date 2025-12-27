@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/purchase.css">
@endpush

@section('content')
<div class="address-container">
    <h2 class="address-title">住所の変更</h2>
    <form method="POST" action="/purchase/address/{{ $item->id }}">
        @csrf
        <div class="address-item">
            <label>郵便番号</label>
            <input type="text" name="postal_code" value="{{ old('postal_code') }}" placeholder="例：111-2222">
        </div>

        <div class="address-item">
            <label>住所</label>
            <input type="text" name="address" value="{{ old('address') }}" placeholder="例：東京都渋谷区〇〇">
        </div>

        <div class="address-item">
            <label>建物名</label>
            <input type="text" name="building" value="{{ old('building') }}" placeholder="例：〇〇マンション101">
        </div>
        <button type="submit" class="address-submit-btn">
            更新する
        </button>
    </form>
</div>
@endsection
