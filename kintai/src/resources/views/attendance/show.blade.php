@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance-show.css') }}">
@endpush

@section('content')
<div class="detail">
  <h1 class="detail__title">
    <span class="detail__bar"></span>
    勤怠詳細
  </h1>

  @if(session('error'))
    <p class="error error--top">{{ session('error') }}</p>
  @endif

  <form method="POST" action="{{ route('attendance.request', $attendance->id) }}">
    @csrf

    <div class="detail__card">
      <div class="row">
        <div class="row__label">名前</div>
        <div class="row__content">
          <div class="value value--text">{{ $userName }}</div>
        </div>
      </div>

      <div class="row">
        <div class="row__label">日付</div>
        <div class="row__content row__content--split">
          <div class="value value--text">{{ $attendance->work_date->format('Y年') }}</div>
          <div class="value value--text">{{ $attendance->work_date->format('n月j日') }}</div>
        </div>
      </div>

      <div class="row">
        <div class="row__label">出勤・退勤</div>
        <div class="row__content row__content--time">
          <input
            class="value value--time {{ $isPending ? 'is-locked' : '' }}"
            type="text"
            name="clock_in"
            value="{{ old('clock_in', optional($attendance->clock_in_at)->format('H:i')) }}"
            {{ $isPending ? 'readonly' : '' }}
          >
          <span class="tilde">〜</span>
          <input
            class="value value--time {{ $isPending ? 'is-locked' : '' }}"
            type="text"
            name="clock_out"
            value="{{ old('clock_out', optional($attendance->clock_out_at)->format('H:i')) }}"
            {{ $isPending ? 'readonly' : '' }}
          >
        </div>
      </div>

      @error('clock_in')
        <p class="error">{{ $message }}</p>
      @enderror

      @foreach($breakRows as $i => $b)
        <div class="row">
          <div class="row__label">
            休憩{{ $i === 0 ? '' : ' ' . ($i + 1) }}
          </div>
          <div class="row__content row__content--time">
            <input
              class="value value--time {{ $isPending ? 'is-locked' : '' }}"
              type="text"
              name="breaks[{{ $i }}][in]"
              value="{{ old("breaks.$i.in", $b['in']) }}"
              {{ $isPending ? 'readonly' : '' }}
            >
            <span class="tilde">〜</span>
            <input
              class="value value--time {{ $isPending ? 'is-locked' : '' }}"
              type="text"
              name="breaks[{{ $i }}][out]"
              value="{{ old("breaks.$i.out", $b['out']) }}"
              {{ $isPending ? 'readonly' : '' }}
            >
          </div>
        </div>

        @error("breaks.$i.in")
          <p class="error">{{ $message }}</p>
        @enderror
      @endforeach

      <div class="row row--note">
        <div class="row__label">備考</div>
        <div class="row__content">
          <textarea
            class="value value--note {{ $isPending ? 'is-locked' : '' }}"
            name="note"
            {{ $isPending ? 'readonly' : '' }}
          >{{ old('note', $attendance->note ?? '') }}</textarea>
        </div>
      </div>

      @error('note')
        <p class="error">{{ $message }}</p>
      @enderror
    </div>

    <div class="detail__actions">
      @if($isPending)
        <p class="cannot-edit">※承認待ちのため修正はできません。</p>
      @else
        <button type="submit" class="btn">修正</button>
      @endif
    </div>
  </form>
</div>
@endsection
