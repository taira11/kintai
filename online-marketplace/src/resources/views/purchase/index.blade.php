@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/purchase.css">
@endpush

@section('content')
<div class="purchase-page">
    <div class="purchase-grid">
        <div class="purchase-left">
            <div class="purchase-item">
                <div class="purchase-item-img">
                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">
                </div>

                <div class="purchase-item-meta">
                    <p class="purchase-item-name">{{ $item->name }}</p>
                    <p class="purchase-item-price">¥{{ number_format($item->price) }}</p>
                </div>
            </div>

            <div class="purchase-divider"></div>

            <div class="purchase-block">
                <p class="purchase-block-title">支払い方法</p>
                <form method="POST" action="/purchase/{{ $item->id }}" class="purchase-form">
                    @csrf
                    <select name="payment_method" id="paymentMethod" class="purchase-select">
                        <option value="konbini" selected>コンビニ支払い</option>
                        <option value="card">カード支払い</option>
                    </select>

                    <div class="purchase-divider"></div>

                    <div class="purchase-block">
                        <div class="purchase-ship-head">
                            <p class="purchase-block-title">配送先</p>
                            <a href="/purchase/address/{{ $item->id }}" class="purchase-change-link">変更する</a>
                        </div>

                        <div class="purchase-address">
                            {{-- 例：〒111-2222 改行 住所 --}}
                            {!! nl2br(e($shippingAddress)) !!}
                        </div>
                    </div>

                    <div class="purchase-divider"></div>

                    <button type="submit" class="purchase-btn purchase-btn-sp">
                        購入する
                    </button>
                </form>
            </div>
        </div>

        <div class="purchase-right">
            <div class="purchase-summary">
                <div class="purchase-summary-row">
                    <span class="purchase-summary-label">商品代金</span>
                    <span class="purchase-summary-value">¥{{ number_format($item->price) }}</span>
                </div>

                <div class="purchase-summary-row">
                    <span class="purchase-summary-label">支払い方法</span>
                    <span class="purchase-summary-value" id="paymentMethodText">コンビニ支払い</span>
                </div>
            </div>

            <button type="button" class="purchase-btn" id="purchaseSubmitBtn">
                購入する
            </button>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('paymentMethod');
    const text = document.getElementById('paymentMethodText');
    const submitBtn = document.getElementById('purchaseSubmitBtn');
    const form = document.querySelector('.purchase-form');

    const label = (v) => v === 'card' ? 'カード支払い' : 'コンビニ支払い';

    text.textContent = label(select.value);

    select.addEventListener('change', () => {
        text.textContent = label(select.value);
    });

    submitBtn.addEventListener('click', () => {
        form.submit();
    });
});
</script>
@endsection
