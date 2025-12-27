@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/items.css">
@endpush

@section('content')
<form action="/sell" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="sell-container">
        @if ($errors->any())
            <div class="error-box">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li class="error-text">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h2 class="sell-title">商品の出品</h2>

        <div class="sell-section">
            <label class="sell-label">商品画像</label>
            <div class="sell-image-area">
                <img id="itemImagePreview" src="/images/no-image.png" class="sell-image-preview">
                <label class="sell-image-btn">
                    画像を選択する
                    <input type="file" name="image" accept="image/*" onchange="previewItemImage(this)">
                </label>
            </div>

            <div class="sell-section">
                <label class="sell-label">商品の詳細</label>
                <p class="sell-sub-label">カテゴリー</p>
                <div class="sell-category-list">
                    @foreach($categories as $category)
                    <div class="category-tag" data-id="{{ $category->id }}">
                        {{ $category->name }}
                    </div>
                    @endforeach
                </div>

                <input type="hidden" name="category_ids" id="selectedCategories">
                @error('category_ids')
                <div class="error-text">{{ $message }}</div>
                @enderror

                <p class="sell-sub-label">商品の状態</p>
                <select name="status" class="sell-select">
                    <option value="">選択してください</option>
                    <option value="4" {{ old('status') == 4 ? 'selected' : '' }}>良好</option>
                    <option value="3" {{ old('status') == 3 ? 'selected' : '' }}>目立った傷や汚れなし</option>
                    <option value="2" {{ old('status') == 2 ? 'selected' : '' }}>やや傷や汚れあり</option>
                    <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>状態が悪い</option>
                </select>
                @error('status')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <div class="sell-section">
                <label class="sell-label">商品名と説明</label>

                <p class="sell-sub-label">商品名</p>
                <input type="text" name="name" class="sell-input" value="{{ old('name') }}">
                @error('name')
                    <div class="error-text">{{ $message }}</div>
                @enderror

                <p class="sell-sub-label">ブランド名</p>
                <input type="text" name="brand" class="sell-input" value="{{ old('brand') }}">
                @error('brand')
                    <div class="error-text">{{ $message }}</div>
                @enderror

                <p class="sell-sub-label">商品の説明</p>
                <textarea name="description" class="sell-textarea">{{ old('description') }}</textarea>
                @error('description')
                    <div class="error-text">{{ $message }}</div>
                @enderror

                <p class="sell-sub-label">販売価格</p>
                <div class="sell-price-wrap">
                    <span class="sell-price-symbol">¥</span>
                    <input type="number" name="price" class="sell-price-input" value="{{ old('price') }}">
                </div>
                @error('price')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>
            <button class="sell-submit-btn">出品する</button>
        </div>
    </div>
</form>

<script>
function previewItemImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        const preview = document.getElementById('itemImagePreview');

        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };

        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener('DOMContentLoaded', () => {

    const tags = document.querySelectorAll('.category-tag');
    const hidden = document.getElementById('selectedCategories');
    let selected = [];

    tags.forEach(tag => {
        tag.addEventListener('click', () => {
            const id = tag.dataset.id;

            if (selected.includes(id)) {
                selected = selected.filter(v => v !== id);
                tag.classList.remove('active');
            } else {
                selected.push(id);
                tag.classList.add('active');
            }

            hidden.value = selected.join(',');
        });
    });

});
</script>
@endsection
