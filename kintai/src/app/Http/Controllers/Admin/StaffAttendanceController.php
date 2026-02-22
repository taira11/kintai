<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StaffAttendanceController extends Controller
{
    public function index(Request $request, User $user)
    {
        $monthParam = $request->query('month');

        $month = $monthParam
            ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
            : now()->startOfMonth();

        $prev = $month->copy()->subMonth()->format('Y-m');
        $next = $month->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ])
            ->with('breaks')
            ->get()
            ->keyBy(fn ($a) => Carbon::parse($a->work_date)->toDateString());

        $rows = [];
        $cursor = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        while ($cursor->lte($end)) {
            $dateKey = $cursor->toDateString();
            $a = $attendances->get($dateKey);

            $clockIn  = $a?->clock_in_at ? Carbon::parse($a->clock_in_at)->format('H:i') : '';
            $clockOut = $a?->clock_out_at ? Carbon::parse($a->clock_out_at)->format('H:i') : '';

            $breakMinutes = 0;
            if ($a) {
                foreach ($a->breaks as $b) {
                    if ($b->break_in_at && $b->break_out_at) {
                        $breakMinutes += Carbon::parse($b->break_in_at)
                            ->diffInMinutes(Carbon::parse($b->break_out_at));
                    }
                }
            }

            $totalMinutes = 0;
            if ($a && $a->clock_in_at && $a->clock_out_at) {
                $workMinutes = $a->clock_in_at->diffInMinutes($a->clock_out_at);
                $totalMinutes = max(0, $workMinutes - $breakMinutes);
            }

            $rows[] = [
                'date_label'    => $cursor->format('m/d') . '(' . $this->jpDow($cursor) . ')',
                'clock_in'      => $clockIn,
                'clock_out'     => $clockOut,
                'break'         => $a ? $this->fmtMinutes($breakMinutes) : '',
                'total'         => ($a && $a->clock_in_at && $a->clock_out_at) ? $this->fmtMinutes($totalMinutes) : '',
                'attendance_id' => $a?->id,
            ];

            $cursor->addDay();
        }

        return view('admin.attendance.staff', [
            'user'       => $user,
            'month'      => $month,
            'monthLabel' => $month->format('Y/m'),
            'prev'       => $prev,
            'next'       => $next,
            'rows'       => $rows,
        ]);
    }

    public function csv(Request $request, User $user)
    {
        $monthParam = $request->query('month');

        $month = $monthParam
            ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
            : now()->startOfMonth();

        $start = $month->copy()->startOfMonth()->toDateString();
        $end   = $month->copy()->endOfMonth()->toDateString();

        $attendances = Attendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$start, $end])
            ->with('breaks')
            ->get()
            ->keyBy(fn ($a) => Carbon::parse($a->work_date)->toDateString());

        $header = ['日付', '出勤', '退勤', '休憩', '合計'];

        $lines = [];
        $lines[] = $this->csvLine($header);

        $cursor = $month->copy()->startOfMonth();
        $last   = $month->copy()->endOfMonth();

        while ($cursor->lte($last)) {
            $dateKey = $cursor->toDateString();
            $a = $attendances->get($dateKey);

            $clockIn  = $a?->clock_in_at ? Carbon::parse($a->clock_in_at)->format('H:i') : '';
            $clockOut = $a?->clock_out_at ? Carbon::parse($a->clock_out_at)->format('H:i') : '';

            $breakMinutes = 0;
            if ($a) {
                foreach ($a->breaks as $b) {
                    if ($b->break_in_at && $b->break_out_at) {
                        $breakMinutes += Carbon::parse($b->break_in_at)
                            ->diffInMinutes(Carbon::parse($b->break_out_at));
                    }
                }
            }

            $total = '';
            if ($a && $a->clock_in_at && $a->clock_out_at) {
                $workMinutes = $a->clock_in_at->diffInMinutes($a->clock_out_at);
                $totalMinutes = max(0, $workMinutes - $breakMinutes);
                $total = $this->fmtMinutes($totalMinutes);
            }

            $lines[] = $this->csvLine([
                $cursor->format('m/d') . '(' . $this->jpDow($cursor) . ')',
                $clockIn,
                $clockOut,
                $a ? $this->fmtMinutes($breakMinutes) : '',
                $total,
            ]);

            $cursor->addDay();
        }

        $csv = implode("\r\n", $lines) . "\r\n";
        $csv = "\xEF\xBB\xBF" . $csv;

        $fileName = sprintf('%s_%s_%s.csv', $user->name, $month->format('Y-m'), 'attendance');

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function fmtMinutes(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return sprintf('%d:%02d', $h, $m);
    }

    private function jpDow(Carbon $d): string
    {
        return ['日', '月', '火', '水', '木', '金', '土'][$d->dayOfWeek];
    }

    private function csvLine(array $cols): string
    {
        $escaped = array_map(function ($v) {
            $v = (string) ($v ?? '');
            $v = str_replace('"', '""', $v);

            return '"' . $v . '"';
        }, $cols);

        return implode(',', $escaped);
    }
}
