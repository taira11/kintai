@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endpush

@section('content')

@php
    $today = now();
    $dateLabel = $today->format('Y年n月j日') . '(' . ['日','月','火','水','木','金','土'][$today->dayOfWeek] . ')';
    $timeLabel = $today->format('H:i');
@endphp

<div class="attendance-page">
  <span class="badge">{{ $status }}</span>

  <p class="date">{{ $dateLabel }}</p>
  <p class="time">{{ $timeLabel }}</p>

  @if($status === '退勤済')
    <p class="done-message">お疲れ様でした。</p>
  @endif

  <div class="btn-row">

    @if($status === '勤務外')
      <form method="POST" action="{{ route('attendance.store') }}">
        @csrf
        <input type="hidden" name="action" value="clock_in">
        <button type="submit" class="btn btn--black">出勤</button>
      </form>
    @endif

    @if($status === '出勤中')
      <form method="POST" action="{{ route('attendance.store') }}">
        @csrf
        <input type="hidden" name="action" value="clock_out">
        <button type="submit" class="btn btn--black">退勤</button>
      </form>

      <form method="POST" action="{{ route('attendance.store') }}">
        @csrf
        <input type="hidden" name="action" value="break_in">
        <button type="submit" class="btn btn--white">休憩入</button>
      </form>
    @endif

    @if($status === '休憩中')
      <form method="POST" action="{{ route('attendance.store') }}">
        @csrf
        <input type="hidden" name="action" value="break_out">
        <button type="submit" class="btn btn--white btn--single">休憩戻</button>
      </form>
    @endif

  </div>
</div>

@endsection
