@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/request-list.css') }}">
@endpush

@section('content')
<div class="req">
  <h1 class="req__title"><span class="req__bar"></span>申請一覧</h1>

  <div class="tabs">
    <a class="tab {{ $tab === 'pending' ? 'is-active' : '' }}"
       href="{{ route('stamp_correction_request.list', ['tab' => 'pending']) }}">承認待ち</a>

    <a class="tab {{ $tab === 'approved' ? 'is-active' : '' }}"
       href="{{ route('stamp_correction_request.list', ['tab' => 'approved']) }}">承認済み</a>
  </div>

  <div class="table-wrap">
    <table class="table">
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
        @forelse($requests as $r)
          <tr>
            <td>{{ $r->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
            <td>{{ optional($r->requester)->name ?? '-' }}</td>
            <td>{{ optional($r->attendance)->work_date?->format('Y/m/d') ?? '-' }}</td>
            <td>{{ $r->reason }}</td>
            <td>{{ $r->created_at->format('Y/m/d') }}</td>
            <td><a class="link" href="#">詳細</a></td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="empty">データがありません</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pager">
    {{ $requests->appends(['tab' => $tab])->links() }}
  </div>
</div>
@endsection
