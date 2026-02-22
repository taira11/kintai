@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endpush

@section('content')
@php
    $rowsByDate = collect($rows)->keyBy(fn ($r) => $r['date']->toDateString());

    $start = $month->copy()->startOfMonth();
    $end   = $month->copy()->endOfMonth();
@endphp

<div class="list-wrap">
  <h1 class="list-title">勤怠一覧</h1>

  <div class="month-bar">
    <a class="month-btn" href="{{ route('attendance.list', ['month' => $prevMonth]) }}">← 前月</a>

    <div class="month-center">
      <span class="month-icon">🗓️</span>
      <span class="month-text">{{ $month->format('Y/m') }}</span>
    </div>

    <a class="month-btn" href="{{ route('attendance.list', ['month' => $nextMonth]) }}">翌月 →</a>
  </div>

  <div class="card">
    <table class="table">
      <thead>
        <tr>
          <th>日付</th>
          <th>出勤</th>
          <th>退勤</th>
          <th>休憩</th>
          <th>合計</th>
          <th>詳細</th>
        </tr>
      </thead>

      <tbody>
        @for($d = $start->copy(); $d->lte($end); $d->addDay())
          @php
              $row = $rowsByDate->get($d->toDateString());

              $clockIn  = ($row && $row['clock_in'])  ? $row['clock_in']->format('H:i')  : '';
              $clockOut = ($row && $row['clock_out']) ? $row['clock_out']->format('H:i') : '';

              $break = '';
              if ($row && $row['break_minutes'] > 0) {
                  $bm = $row['break_minutes'];
                  $break = intdiv($bm, 60) . ':' . str_pad($bm % 60, 2, '0', STR_PAD_LEFT);
              }

              $total = '';
              if ($row && $row['work_minutes'] !== null) {
                  $wm = $row['work_minutes'];
                  $total = intdiv($wm, 60) . ':' . str_pad($wm % 60, 2, '0', STR_PAD_LEFT);
              }
          @endphp

          <tr>
            <td>{{ $d->format('m/d') }}({{ ['日','月','火','水','木','金','土'][$d->dayOfWeek] }})</td>
            <td>{{ $clockIn }}</td>
            <td>{{ $clockOut }}</td>
            <td>{{ $break }}</td>
            <td>{{ $total }}</td>
            <td>
              @if($row)
                <a class="detail-link" href="{{ route('attendance.show', $row['id']) }}">詳細</a>
              @else
                <a class="detail-link" href="{{ route('attendance.showByDate', ['date' => $d->toDateString()]) }}">詳細</a>
              @endif
            </td>
          </tr>
        @endfor
      </tbody>
    </table>
  </div>
</div>
@endsection
