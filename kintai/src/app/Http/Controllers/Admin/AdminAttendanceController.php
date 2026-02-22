<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $dateStr = $request->query('date') ?? now()->toDateString();
        $date = Carbon::parse($dateStr)->startOfDay();

        $prev = $date->copy()->subDay()->toDateString();
        $next = $date->copy()->addDay()->toDateString();

        $attendances = Attendance::whereDate('work_date', $date->toDateString())
            ->orderBy('user_id')
            ->get();

        $userIds = $attendances->pluck('user_id')->unique()->values();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $rows = $attendances->map(function ($a) use ($users) {
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
                'attendance_id' => $a->id,
                'name'          => $users[$a->user_id]->name ?? '---',
                'clock_in'      => $a->clock_in_at ? $a->clock_in_at->format('H:i') : '',
                'clock_out'     => $a->clock_out_at ? $a->clock_out_at->format('H:i') : '',
                'break'         => $this->minutesToHhmm($breakMinutes),
                'total'         => $workMinutes === null ? '' : $this->minutesToHhmm($workMinutes),
            ];
        });

        return view('admin.attendance.list', [
            'date' => $date,
            'prev' => $prev,
            'next' => $next,
            'rows' => $rows,
        ]);
    }

    public function show(Attendance $attendance)
    {
        $attendance->load(['user', 'breaks']);

        $breaks = $attendance->breaks->sortBy('id')->values();
        $break1 = $breaks->get(0);
        $break2 = $breaks->get(1);

        return view('admin.attendance.show', [
            'attendance'  => $attendance,
            'userName'    => $attendance->user?->name ?? '',
            'dateY'       => $attendance->work_date ? Carbon::parse($attendance->work_date)->format('Y年') : '',
            'dateMD'      => $attendance->work_date ? Carbon::parse($attendance->work_date)->format('n月j日') : '',
            'clockIn'     => $attendance->clock_in_at ? Carbon::parse($attendance->clock_in_at)->format('H:i') : '',
            'clockOut'    => $attendance->clock_out_at ? Carbon::parse($attendance->clock_out_at)->format('H:i') : '',
            'break1Start' => $break1?->break_in_at ? Carbon::parse($break1->break_in_at)->format('H:i') : '',
            'break1End'   => $break1?->break_out_at ? Carbon::parse($break1->break_out_at)->format('H:i') : '',
            'break2Start' => $break2?->break_in_at ? Carbon::parse($break2->break_in_at)->format('H:i') : '',
            'break2End'   => $break2?->break_out_at ? Carbon::parse($break2->break_out_at)->format('H:i') : '',
            'note'        => $attendance->note ?? ($attendance->remark ?? ''),
            'break1Id'    => $break1?->id,
            'break2Id'    => $break2?->id,
        ]);
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'clock_in'      => ['nullable', 'date_format:H:i'],
                'clock_out'     => ['nullable', 'date_format:H:i'],
                'break1_start'  => ['nullable', 'date_format:H:i'],
                'break1_end'    => ['nullable', 'date_format:H:i'],
                'break2_start'  => ['nullable', 'date_format:H:i'],
                'break2_end'    => ['nullable', 'date_format:H:i'],
                'note'          => ['required', 'string'],
            ],
            [
                'note.required' => '備考を記入してください',
            ]
        );

        $validator->after(function ($v) use ($request, $attendance) {
            $workDate = $this->workDateString($attendance);

            $clockIn  = $this->toDateTime($workDate, $request->input('clock_in'));
            $clockOut = $this->toDateTime($workDate, $request->input('clock_out'));

            $b1s = $this->toDateTime($workDate, $request->input('break1_start'));
            $b1e = $this->toDateTime($workDate, $request->input('break1_end'));
            $b2s = $this->toDateTime($workDate, $request->input('break2_start'));
            $b2e = $this->toDateTime($workDate, $request->input('break2_end'));

            if ($clockIn && $clockOut && $clockIn->gt($clockOut)) {
                $v->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
            }

            if ($clockOut && $b1s && $b1s->gt($clockOut)) {
                $v->errors()->add('break1_start', '休憩時間が不適切な値です');
            }
            if ($clockOut && $b2s && $b2s->gt($clockOut)) {
                $v->errors()->add('break2_start', '休憩時間が不適切な値です');
            }

            if ($clockOut && $b1e && $b1e->gt($clockOut)) {
                $v->errors()->add('break1_end', '休憩時間もしくは退勤時間が不適切な値です');
            }
            if ($clockOut && $b2e && $b2e->gt($clockOut)) {
                $v->errors()->add('break2_end', '休憩時間もしくは退勤時間が不適切な値です');
            }
        });

        $validator->validate();

        DB::transaction(function () use ($request, $attendance) {
            $attendance->load('breaks');

            $workDate = $this->workDateString($attendance);

            $attendance->clock_in_at  = $this->toDateTime($workDate, $request->input('clock_in'));
            $attendance->clock_out_at = $this->toDateTime($workDate, $request->input('clock_out'));

            if ($attendance->isFillable('note') || array_key_exists('note', $attendance->getAttributes())) {
                $attendance->note = $request->input('note');
            } else {
                $attendance->remark = $request->input('note');
            }

            $attendance->save();

            $breaks = $attendance->breaks->sortBy('id')->values();

            $this->upsertBreak(
                $attendance,
                $breaks->get(0),
                $this->toDateTime($workDate, $request->input('break1_start')),
                $this->toDateTime($workDate, $request->input('break1_end'))
            );

            $this->upsertBreak(
                $attendance,
                $breaks->get(1),
                $this->toDateTime($workDate, $request->input('break2_start')),
                $this->toDateTime($workDate, $request->input('break2_end'))
            );
        });

        return redirect()
            ->route('admin.attendance.show', ['attendance' => $attendance->id])
            ->with('message', '修正しました');
    }

    public function staff(Request $request, User $user)
    {
        $ym = $request->query('month') ?? now()->format('Y-m');
        $month = Carbon::createFromFormat('Y-m', $ym)->startOfMonth();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->with(['breaks' => function ($q) {
                $q->orderBy('id');
            }])
            ->get()
            ->keyBy(fn ($a) => Carbon::parse($a->work_date)->toDateString());

        $rows = [];

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dateStr = $d->toDateString();
            $a = $attendances->get($dateStr);

            $breakMinutes = 0;
            if ($a) {
                foreach ($a->breaks as $b) {
                    if ($b->break_in_at && $b->break_out_at) {
                        $breakMinutes += $b->break_in_at->diffInMinutes($b->break_out_at);
                    }
                }
            }

            $workMinutes = null;
            if ($a && $a->clock_in_at && $a->clock_out_at) {
                $total = $a->clock_in_at->diffInMinutes($a->clock_out_at);
                $workMinutes = max(0, $total - $breakMinutes);
            }

            $rows[] = [
                'date'          => $d->copy(),
                'date_label'    => $d->format('m/d') . '(' . ['日', '月', '火', '水', '木', '金', '土'][$d->dayOfWeek] . ')',
                'attendance_id' => $a?->id,
                'clock_in'      => $a?->clock_in_at ? $a->clock_in_at->format('H:i') : '',
                'clock_out'     => $a?->clock_out_at ? $a->clock_out_at->format('H:i') : '',
                'break'         => $breakMinutes > 0 ? $this->minutesToHhmm($breakMinutes) : '',
                'total'         => $workMinutes === null ? '' : $this->minutesToHhmm($workMinutes),
            ];
        }

        $prev = $month->copy()->subMonth()->format('Y-m');
        $next = $month->copy()->addMonth()->format('Y-m');

        return view('admin.attendance.staff', [
            'user'       => $user,
            'month'      => $month,
            'monthLabel' => $month->format('Y/m'),
            'prev'       => $prev,
            'next'       => $next,
            'rows'       => $rows,
        ]);
    }

    public function showByDate(User $user, string $date)
    {
        $date = Carbon::parse($date)->toDateString();

        $attendance = Attendance::firstOrCreate([
            'user_id'   => $user->id,
            'work_date' => $date,
        ]);

        return redirect()->route('admin.attendance.show', ['attendance' => $attendance->id]);
    }

    public function csv(Request $request, User $user)
    {
        $ym = $request->query('month') ?? now()->format('Y-m');
        $month = Carbon::createFromFormat('Y-m', $ym);

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('work_date', $month->year)
            ->whereMonth('work_date', $month->month)
            ->with('breaks')
            ->get();

        $csv = "日付,出勤,退勤\n";

        foreach ($attendances as $a) {
            $csv .= $a->work_date->format('Y-m-d') . ",";
            $csv .= optional($a->clock_in_at)->format('H:i') . ",";
            $csv .= optional($a->clock_out_at)->format('H:i') . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename=\"attendance.csv\"');
    }

    private function minutesToHhmm(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return sprintf('%d:%02d', $h, $m);
    }

    private function workDateString(Attendance $attendance): string
    {
        $wd = $attendance->work_date;

        if ($wd instanceof Carbon) {
            return $wd->toDateString();
        }

        return Carbon::parse($wd)->toDateString();
    }

    private function toDateTime(string $workDate, ?string $time): ?Carbon
    {
        $time = trim((string) $time);

        if ($time === '') {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d H:i', $workDate . ' ' . $time);
    }

    private function upsertBreak(
        Attendance $attendance,
        ?BreakTime $breakModel,
        ?Carbon $start,
        ?Carbon $end
    ): void {
        if (! $start && ! $end) {
            return;
        }

        if ($breakModel) {
            $breakModel->break_in_at  = $start;
            $breakModel->break_out_at = $end;
            $breakModel->save();

            return;
        }

        $attendance->breaks()->create([
            'break_in_at'  => $start,
            'break_out_at' => $end,
        ]);
    }
}
