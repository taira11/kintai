@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-staff-attendance.css') }}">
@endpush

@section('content')
<div class="staff-att">
  <h1 class="staff-att__title">
    <span class="staff-att__bar"></span>
    {{ $user->name }}さんの勤怠
  </h1>

  <div class="month-nav">
    <a class="month-nav__btn"
       href="{{ route('admin.attendance.staff', ['user' => $user->id, 'month' => $prev]) }}">
      ← 前月
    </a>

    <div class="month-nav__center">
      <span class="month-nav__icon">🗓️</span>
      <span class="month-nav__month">{{ $monthLabel }}</span>
    </div>

    <a class="month-nav__btn"
       href="{{ route('admin.attendance.staff', ['user' => $user->id, 'month' => $next]) }}">
      翌月 →
    </a>
  </div>

  <div class="staff-att__table-wrap">
    <table class="staff-att__table">
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
        @foreach($rows as $r)
          <tr>
            <td>{{ $r['date_label'] }}</td>
            <td>{{ $r['clock_in'] }}</td>
            <td>{{ $r['clock_out'] }}</td>
            <td>{{ $r['break'] }}</td>
            <td>{{ $r['total'] }}</td>
            <td>
              @if(!empty($r['attendance_id']))
                <a class="staff-att__link"
                   href="{{ route('admin.attendance.show', ['attendance' => $r['attendance_id']]) }}">
                  詳細
                </a>
              @else
                <a class="staff-att__link"
                   href="{{ route('admin.attendance.staff.showByDate', [
                       'user' => $user->id,
                       'date' => $r['date']->toDateString(),
                   ]) }}">
                  詳細
                </a>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="staff-att__csv">
    <a class="staff-att__csv-btn"
       href="{{ route('admin.attendance.staff.csv', [
           'user'  => $user->id,
           'month' => request('month', $month->format('Y-m')),
       ]) }}">
      CSV出力
    </a>
  </div>
</div>
@endsection
