@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-list.css') }}">
@endpush

@section('content')
<div class="admin-list">

  <h1 class="admin-list__title">
    <span class="admin-list__bar"></span>
    {{ $date->format('Y年n月j日') }}の勤怠
  </h1>

  <div class="date-nav">
    <a class="date-nav__btn" href="{{ route('admin.attendance.list', ['date' => $prev]) }}">← 前日</a>

    <div class="date-nav__center">
      <span class="date-nav__icon">🗓️</span>
      <span class="date-nav__date">{{ $date->format('Y/m/d') }}</span>
    </div>

    <a class="date-nav__btn" href="{{ route('admin.attendance.list', ['date' => $next]) }}">翌日 →</a>
  </div>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>名前</th>
          <th>出勤</th>
          <th>退勤</th>
          <th>休憩</th>
          <th>合計</th>
          <th>詳細</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $r)
          <tr>
            <td>{{ $r['name'] }}</td>
            <td>{{ $r['clock_in'] }}</td>
            <td>{{ $r['clock_out'] }}</td>
            <td>{{ $r['break'] }}</td>
            <td>{{ $r['total'] }}</td>
            <td>
             <a href="{{ url('/admin/attendance/'.$r['attendance_id']) }}">詳細</a>
            </td>
          </tr>
        @empty
          <tr>
            <td class="empty" colspan="6">データがありません</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

</div>
@endsection
