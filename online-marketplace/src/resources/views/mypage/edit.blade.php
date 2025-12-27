@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/mypage.css">
@endpush

@section('content')
<div class="profile-container">
    <form action="/mypage/edit" method="POST" enctype="multipart/form-data">
        @csrf
        <h2 class="profile-title">プロフィール設定</h2>
        <div class="profile-image-wrapper">
            <img id="profilePreview" src="{{ $profile && $profile->profile_image ? asset('storage/' . $profile->profile_image) : '/images/default-icon.png' }}" class="profile-image">
            <label class="profile-image-btn">
                画像を選択する
                <input type="file" name="image" accept="image/*" onchange="previewImage(this)">
            </label>
        </div>
        @if ($errors->has('image') && !request()->hasFile('image'))
            <p class="error-text">{{ $errors->first('image') }}</p>
        @endif

        <div class="profile-item">
            <label>ユーザー名</label>
            <input type="text" name="nickname" value="{{ old('nickname', Auth::user()->profile->nickname ?? '') }}">
        </div>
        @error('nickname')
            <p class="error-text">{{ $message }}</p>
        @enderror

        <div class="profile-item">
            <label>郵便番号</label>
            <input type="text" name="postal_code" value="{{ old('postal_code', Auth::user()->profile->postal_code ?? '') }}">
        </div>
        @error('postal_code')
            <p class="error-text">{{ $message }}</p>
        @enderror

        <div class="profile-item">
            <label>住所</label>
            <input type="text" name="address" value="{{ old('address', Auth::user()->profile->address ?? '') }}">
        </div>
        @error('address')
            <p class="error-text">{{ $message }}</p>
        @enderror

        <div class="profile-item">
            <label>建物名</label>
            <input type="text" name="building" value="{{ old('building', Auth::user()->profile->building ?? '') }}">
        </div>
        <button type="submit" class="profile-submit-btn">更新する</button>
    </form>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function (e) {
            document.getElementById('profilePreview').src = e.target.result;
        };

        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
