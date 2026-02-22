@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-approve.css') }}">
@endpush

@section('content')
@php
    $cr = $changeRequest ?? null;
    $isApproved = $cr && ($cr->status ?? '') === 'approved';
@endphp

<div class="approve">
  <h1 class="approve__title">
    <span class="approve__bar"></span>
    勤怠詳細
  </h1>

  <div class="approve__card">
    <div class="approve__row">
      <div class="approve__label">名前</div>
      <div class="approve__value">{{ $userName }}</div>
    </div>

    <div class="approve__row">
      <div class="approve__label">日付</div>
      <div class="approve__value approve__date">
        <span>{{ $dateY }}</span>
        <span>{{ $dateMD }}</span>
      </div>
    </div>

    <div class="approve__row">
      <div class="approve__label">出勤・退勤</div>
      <div class="approve__value approve__time">
        <span>{{ $clockIn }}</span>
        <span class="approve__tilde">〜</span>
        <span>{{ $clockOut }}</span>
      </div>
    </div>

    <div class="approve__row">
      <div class="approve__label">休憩</div>
      <div class="approve__value approve__time">
        <span>{{ $break1['start'] }}</span>
        <span class="approve__tilde">〜</span>
        <span>{{ $break1['end'] }}</span>
      </div>
    </div>

    <div class="approve__row">
      <div class="approve__label">休憩2</div>
      <div class="approve__value approve__time">
        <span>{{ $break2['start'] }}</span>
        <span class="approve__tilde">〜</span>
        <span>{{ $break2['end'] }}</span>
      </div>
    </div>

    <div class="approve__row approve__row--last">
      <div class="approve__label">備考</div>
      <div class="approve__value">{{ $note }}</div>
    </div>
  </div>

  <div class="approve__actions">
    <form
      id="approveForm"
      method="POST"
      action="{{ route('admin.correction.approve', ['changeRequest' => $cr->id]) }}"
    >
      @csrf

      <button
        id="approveBtn"
        type="submit"
        class="approve-btn {{ $isApproved ? 'is-approved' : '' }}"
        {{ $isApproved ? 'disabled' : '' }}
      >
        {{ $isApproved ? '承認済み' : '承認' }}
      </button>
    </form>

    <p id="approveMsg" class="approve-msg" style="display:none;"></p>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('approveForm');
  const btn = document.getElementById('approveBtn');
  const msg = document.getElementById('approveMsg');

  if (!form || !btn) return;
  if (btn.disabled) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    btn.disabled = true;
    msg.style.display = 'none';

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
          'Accept': 'application/json',
        },
      });

      if (!res.ok) throw new Error('request failed');

      btn.textContent = '承認済み';
      btn.classList.add('is-approved');
      btn.disabled = true;
    } catch (err) {
      btn.disabled = false;
      msg.textContent = '承認に失敗しました。もう一度お試しください。';
      msg.style.display = 'block';
    }
  });
});
</script>
@endsection
