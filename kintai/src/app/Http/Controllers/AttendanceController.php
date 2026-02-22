<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceDetailChangeRequest;
use App\Models\Attendance;
use App\Models\AttendanceChangeRequest;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        $openBreak = null;

        if ($attendance) {
            $openBreak = BreakTime::where('attendance_id', $attendance->id)
                ->whereNull('break_out_at')
                ->latest('id')
                ->first();
        }

        if (! $attendance || ! $attendance->clock_in_at) {
            $status = '勤務外';
        } elseif ($attendance->clock_out_at) {
            $status = '退勤済';
        } elseif ($openBreak) {
            $status = '休憩中';
        } else {
            $status = '出勤中';
        }

        return view('attendance.index', compact('attendance', 'status'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'action' => ['required', 'in:clock_in,break_in,break_out,clock_out'],
        ]);

        $user = auth()->user();
        $today = now()->toDateString();
        $action = $request->input('action');

        $attendance = Attendance::firstOrCreate([
            'user_id'   => $user->id,
            'work_date' => $today,
        ]);

        $openBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_out_at')
            ->latest('id')
            ->first();

        if (! $attendance->clock_in_at) {
            $status = '勤務外';
        } elseif ($attendance->clock_out_at) {
            $status = '退勤済';
        } elseif ($openBreak) {
            $status = '休憩中';
        } else {
            $status = '出勤中';
        }

        if ($action === 'clock_in') {
            if ($status !== '勤務外') {
                return back()->with('error', '出勤できません（すでに出勤済みです）');
            }

            $attendance->update(['clock_in_at' => now()]);

            return back()->with('success', '出勤しました');
        }

        if ($action === 'break_in') {
            if ($status !== '出勤中') {
                return back()->with('error', '休憩に入れません（出勤中のみ）');
            }

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_in_at'   => now(),
                'break_out_at'  => null,
            ]);

            return back()->with('success', '休憩に入りました');
        }

        if ($action === 'break_out') {
            if ($status !== '休憩中' || ! $openBreak) {
                return back()->with('error', '休憩を終了できません（休憩中のみ）');
            }

            $openBreak->update(['break_out_at' => now()]);

            return back()->with('success', '休憩を終了しました');
        }

        if ($action === 'clock_out') {
            if ($status !== '出勤中') {
                return back()->with('error', '退勤できません（出勤中のみ）');
            }

            $attendance->update(['clock_out_at' => now()]);

            return back()->with('success', 'お疲れ様でした。');
        }

        return back()->with('error', '不正な操作です');
    }

    public function list(Request $request)
    {
        $user = auth()->user();

        $ym = $request->query('month') ?? now()->format('Y-m');
        $month = Carbon::createFromFormat('Y-m', $ym)->startOfMonth();

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('work_date')
            ->get();

        $rows = $attendances->map(function ($a) {
            $breaks = BreakTime::where('attendance_id', $a->id)->get();

            $breakMinutes = 0;

            foreach ($breaks as $b) {
                if ($b->break_in_at && $b->break_out_at) {
                    $breakMinutes += $b->break_in_at->diffInMinutes($b->break_out_at);
                }
            }

            $workMinutes = null;

            if ($a->clock_in_at && $a->clock_out_at) {
                $total = $a->clock_in_at->diffInMinutes($a->clock_out_at);
                $workMinutes = max(0, $total - $breakMinutes);
            }

            return [
                'id'            => $a->id,
                'date'          => Carbon::parse($a->work_date),
                'clock_in'      => $a->clock_in_at,
                'clock_out'     => $a->clock_out_at,
                'break_minutes' => $breakMinutes,
                'work_minutes'  => $workMinutes,
            ];
        });

        $prevMonth = $month->copy()->subMonth()->format('Y-m');
        $nextMonth = $month->copy()->addMonth()->format('Y-m');

        return view('attendance.list', [
            'month'     => $month,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'rows'      => $rows,
        ]);
    }

    public function show($id)
    {
        $user = auth()->user();

        $attendance = Attendance::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $breaks = BreakTime::where('attendance_id', $attendance->id)
            ->orderBy('break_in_at')
            ->get();

        $breakRows = $breaks->map(function ($b) {
            return [
                'in'  => $b->break_in_at ? $b->break_in_at->format('H:i') : '',
                'out' => $b->break_out_at ? $b->break_out_at->format('H:i') : '',
            ];
        })->values()->all();

        $breakRows[] = ['in' => '', 'out' => ''];

        while (count($breakRows) < 2) {
            $breakRows[] = ['in' => '', 'out' => ''];
        }

        $breakMinutes = 0;

        foreach ($breaks as $b) {
            if ($b->break_in_at && $b->break_out_at) {
                $breakMinutes += $b->break_in_at->diffInMinutes($b->break_out_at);
            }
        }

        $workMinutes = null;

        if ($attendance->clock_in_at && $attendance->clock_out_at) {
            $total = $attendance->clock_in_at->diffInMinutes($attendance->clock_out_at);
            $workMinutes = max(0, $total - $breakMinutes);
        }

        $isPending = AttendanceChangeRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->exists();

        return view('attendance.show', [
            'attendance'   => $attendance,
            'breaks'       => $breaks,
            'breakRows'    => $breakRows,
            'breakMinutes' => $breakMinutes,
            'workMinutes'  => $workMinutes,
            'isPending'    => $isPending,
            'userName'     => $user->name,
        ]);
    }

    public function requestChange(AttendanceDetailChangeRequest $request, $id)
    {
        $user = auth()->user();

        $attendance = Attendance::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (
            AttendanceChangeRequest::where('attendance_id', $attendance->id)
                ->where('status', 'pending')
                ->exists()
        ) {
            return back()->with('error', '承認待ちのため修正はできません。');
        }

        $workDate = $attendance->work_date->toDateString();
        $note = trim((string) $request->input('note', ''));
        $clockIn = trim((string) $request->input('clock_in'));
        $clockOut = trim((string) $request->input('clock_out'));
        $breaks = $request->input('breaks', []);

        $created = 0;

        if ($clockIn !== '') {
            $after = Carbon::createFromFormat('Y-m-d H:i', $workDate . ' ' . $clockIn);
            $before = $attendance->clock_in_at;

            if (! $before || ! $before->equalTo($after)) {
                AttendanceChangeRequest::create([
                    'attendance_id' => $attendance->id,
                    'requested_by'  => $user->id,
                    'status'        => 'pending',
                    'target_field'  => 'clock_in_at',
                    'before_value'  => $before,
                    'after_value'   => $after,
                    'reason'        => $note,
                ]);

                $created++;
            }
        }

        if ($clockOut !== '') {
            $after = Carbon::createFromFormat('Y-m-d H:i', $workDate . ' ' . $clockOut);
            $before = $attendance->clock_out_at;

            if (! $before || ! $before->equalTo($after)) {
                AttendanceChangeRequest::create([
                    'attendance_id' => $attendance->id,
                    'requested_by'  => $user->id,
                    'status'        => 'pending',
                    'target_field'  => 'clock_out_at',
                    'before_value'  => $before,
                    'after_value'   => $after,
                    'reason'        => $note,
                ]);

                $created++;
            }
        }

        foreach ($breaks as $i => $b) {
            $bin = trim((string) ($b['in'] ?? ''));
            $bout = trim((string) ($b['out'] ?? ''));

            if ($bin === '' && $bout === '') {
                continue;
            }

            $n = $i + 1;

            if ($bin !== '') {
                $after = Carbon::createFromFormat('Y-m-d H:i', $workDate . ' ' . $bin);

                AttendanceChangeRequest::create([
                    'attendance_id' => $attendance->id,
                    'requested_by'  => $user->id,
                    'status'        => 'pending',
                    'target_field'  => "break_in_at_{$n}",
                    'before_value'  => null,
                    'after_value'   => $after,
                    'reason'        => $note,
                ]);

                $created++;
            }

            if ($bout !== '') {
                $after = Carbon::createFromFormat('Y-m-d H:i', $workDate . ' ' . $bout);

                AttendanceChangeRequest::create([
                    'attendance_id' => $attendance->id,
                    'requested_by'  => $user->id,
                    'status'        => 'pending',
                    'target_field'  => "break_out_at_{$n}",
                    'before_value'  => null,
                    'after_value'   => $after,
                    'reason'        => $note,
                ]);

                $created++;
            }
        }

        if ($created === 0) {
            return back()->with('error', '変更内容がありません。')->withInput();
        }

        return redirect()
            ->route('attendance.show', $attendance->id)
            ->with('success', '修正申請を送信しました。')
            ->withInput();
    }

    public function showByDate(string $date)
    {
        $user = auth()->user();

        $workDate = Carbon::parse($date)->toDateString();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $workDate],
            ['note' => null]
        );

        return $this->show($attendance->id);
    }
}
