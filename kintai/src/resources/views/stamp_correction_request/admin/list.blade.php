@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-correction-list.css') }}">
@endpush

@section('content')
<div class="req">

  <h1 class="req__title">
    <span class="req__bar"></span>
    申請一覧
  </h1>

  <div class="req__tabs">
    <a
      class="req__tab {{ $tab === 'pending' ? 'is-active' : '' }}"
      href="{{ route('admin.correction.list', ['tab' => 'pending']) }}"
    >
      承認待ち
    </a>

    <a
      class="req__tab {{ $tab === 'approved' ? 'is-active' : '' }}"
      href="{{ route('admin.correction.list', ['tab' => 'approved']) }}"
    >
      承認済み
    </a>
  </div>

  <div class="req__line"></div>

  <div class="req__table-wrap">
    <table class="req__table">
      <thead>
        <tr>
          <th>状態</th>
          <th>名前</th>
          <th>対象日時</th>
          <th>申請理由</th>
          <th>申請日時</th>
          <th>詳細</th>
        </tr>
      </thead>

      <tbody>
        @forelse($rows as $r)
          <tr>
            <td>{{ $r['status_label'] }}</td>
            <td>{{ $r['name'] }}</td>
            <td>{{ $r['target_date'] }}</td>
            <td>{{ $r['reason'] }}</td>
            <td>{{ $r['request_date'] }}</td>
            <td>
              @if(!empty($r['id']))
                <a
                  class="req__link"
                  href="{{ route('admin.correction.approve.show', ['changeRequest' => $r['id']]) }}"
                >
                  詳細
                </a>
              @else
                <span>-</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td class="req__empty" colspan="6">
              データがありません
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

</div>
@endsection
