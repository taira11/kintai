@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-show.css') }}">
@endpush

@section('content')
<div class="ad">
  <h1 class="ad__title">
    <span class="ad__bar"></span>
    勤怠詳細
  </h1>

  @if($errors->any())
    <ul class="ad__errors">
      @foreach($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  @endif

  @if(!empty($isLocked))
    <p class="ad__lock">承認待ちのため修正はできません。</p>
  @endif

  <form method="POST" action="{{ route('admin.attendance.update', ['attendance' => $attendance->id]) }}">
    @csrf

    <div class="ad__card">
      <div class="ad__row">
        <div class="ad__label">名前</div>
        <div class="ad__value">{{ $userName }}</div>
      </div>

      <div class="ad__row">
        <div class="ad__label">日付</div>
        <div class="ad__value ad__date">
          <span>{{ $dateY }}</span>
          <span>{{ $dateMD }}</span>
        </div>
      </div>

      <div class="ad__row">
        <div class="ad__label">出勤・退勤</div>
        <div class="ad__value ad__time">
          <input
            class="ad__input"
            type="text"
            name="clock_in"
            value="{{ old('clock_in', $clockIn) }}"
            placeholder="00:00"
            {{ !empty($isLocked) ? 'disabled' : '' }}
          >
          <span class="ad__tilde">〜</span>
          <input
            class="ad__input"
            type="text"
            name="clock_out"
            value="{{ old('clock_out', $clockOut) }}"
            placeholder="00:00"
            {{ !empty($isLocked) ? 'disabled' : '' }}
          >
        </div>
      </div>

      <div class="ad__row">
        <div class="ad__label">休憩</div>
        <div class="ad__value ad__time">
          <input
            class="ad__input"
            type="text"
            name="break1_start"
            value="{{ old('break1_start', $break1Start) }}"
            placeholder="00:00"
            {{ !empty($isLocked) ? 'disabled' : '' }}
          >
          <span class="ad__tilde">〜</span>
          <input
            class="ad__input"
            type="text"
            name="break1_end"
            value="{{ old('break1_end', $break1End) }}"
            placeholder="00:00"
            {{ !empty($isLocked) ? 'disabled' : '' }}
          >
        </div>
      </div>

      <div class="ad__row">
        <div class="ad__label">休憩2</div>
        <div class="ad__value ad__time">
          <input
            class="ad__input"
            type="text"
            name="break2_start"
            value="{{ old('break2_start', $break2Start) }}"
            placeholder="00:00"
            {{ !empty($isLocked) ? 'disabled' : '' }}
          >
          <span class="ad__tilde">〜</span>
          <input
            class="ad__input"
            type="text"
            name="break2_end"
            value="{{ old('break2_end', $break2End) }}"
            placeholder="00:00"
            {{ !empty($isLocked) ? 'disabled' : '' }}
          >
        </div>
      </div>

      <div class="ad__row ad__row--last">
        <div class="ad__label">備考</div>
        <div class="ad__value">
          <textarea
            class="ad__textarea"
            name="note"
            placeholder=""
            {{ !empty($isLocked) ? 'disabled' : '' }}
          >{{ old('note', $note) }}</textarea>
        </div>
      </div>
    </div>

    <div class="ad__actions">
      <button class="ad__btn" type="submit" {{ !empty($isLocked) ? 'disabled' : '' }}>修正</button>
    </div>
  </form>
</div>
@endsection
