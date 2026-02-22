@extends('layouts.auth')

@section('content')
<div class="container">
    <h1>勤怠登録</h1>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if(session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    <p>本日：{{ now()->format('Y-m-d') }}</p>

    <p>
        現在の状態：
        <strong>
            @if(!$attendance)
                未出勤
            @elseif($attendance->clock_out_at)
                退勤済
            @else
                出勤中
            @endif
        </strong>
    </p>

    @if(!$attendance)
        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf
            <input type="hidden" name="action" value="clock_in">
            <button type="submit">出勤</button>
        </form>
    @endif

    @if($attendance && !$attendance->clock_out_at)
        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf
            <input type="hidden" name="action" value="clock_out">
            <button type="submit">退勤</button>
        </form>
    @endif
</div>
@endsection
