@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-staff-list.css') }}">
@endpush

@section('content')
<div class="staff">
  <h1 class="staff__title">
    <span class="staff__bar"></span>
    スタッフ一覧
  </h1>

  <div class="staff__table-wrap">
    <table class="staff__table">
      <thead>
        <tr>
          <th>名前</th>
          <th>メールアドレス</th>
          <th>月次勤怠</th>
        </tr>
      </thead>

      <tbody>
        @forelse($users as $u)
          <tr>
            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td>
              <a
                class="staff__link"
                href="{{ route('admin.attendance.staff', ['user' => $u->id]) }}"
              >
                詳細
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td class="staff__empty" colspan="3">データがありません</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
