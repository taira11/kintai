<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceChangeRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending');
        $status = $tab === 'approved' ? 'approved' : 'pending';

        $requests = AttendanceChangeRequest::query()
            ->with(['requester', 'attendance.user'])
            ->where('status', $status)
            ->orderByDesc('id')
            ->get();

        $rows = $requests->map(function ($r) {
            $targetDate = $r->attendance?->work_date;

            return [
                'status_label' => $r->status === 'approved' ? '承認済み' : '承認待ち',
                'name'         => $r->requester?->name ?? '',
                'target_date'  => $targetDate ? Carbon::parse($targetDate)->format('Y/m/d') : '',
                'reason'       => $r->reason ?? '',
                'request_date' => $r->created_at ? $r->created_at->format('Y/m/d') : '',
                'id'           => $r->id,
            ];
        });

        return view('stamp_correction_request.admin.list', compact('tab', 'rows'));
    }

    public function show(AttendanceChangeRequest $changeRequest)
    {
        $changeRequest->load(['requester', 'attendance.user', 'attendance.breaks']);

        $attendance = $changeRequest->attendance;

        $userName = $changeRequest->requester?->name
            ?? $attendance?->user?->name
            ?? '';

        $dateY = '';
        $dateMD = '';

        if ($attendance?->work_date) {
            $d = Carbon::parse($attendance->work_date);
            $dateY  = $d->format('Y年');
            $dateMD = $d->format('n月j日');
        }

        $clockIn  = $attendance?->clock_in_at ? Carbon::parse($attendance->clock_in_at)->format('H:i') : '';
        $clockOut = $attendance?->clock_out_at ? Carbon::parse($attendance->clock_out_at)->format('H:i') : '';

        $breaks = collect($attendance?->breaks ?? [])->sortBy('id')->values();

        $break1 = ['start' => '', 'end' => ''];
        $break2 = ['start' => '', 'end' => ''];

        if ($breaks->get(0)) {
            $break1['start'] = $breaks[0]->break_in_at ? Carbon::parse($breaks[0]->break_in_at)->format('H:i') : '';
            $break1['end']   = $breaks[0]->break_out_at ? Carbon::parse($breaks[0]->break_out_at)->format('H:i') : '';
        }

        if ($breaks->get(1)) {
            $break2['start'] = $breaks[1]->break_in_at ? Carbon::parse($breaks[1]->break_in_at)->format('H:i') : '';
            $break2['end']   = $breaks[1]->break_out_at ? Carbon::parse($breaks[1]->break_out_at)->format('H:i') : '';
        }

        $note = $attendance?->note ?? '';

        return view('stamp_correction_request.admin.approve', [
            'changeRequest' => $changeRequest,
            'attendance'    => $attendance,
            'userName'      => $userName,
            'dateY'         => $dateY,
            'dateMD'        => $dateMD,
            'clockIn'       => $clockIn,
            'clockOut'      => $clockOut,
            'break1'        => $break1,
            'break2'        => $break2,
            'note'          => $note,
        ]);
    }

    public function approve(AttendanceChangeRequest $changeRequest)
    {
        if ($changeRequest->status === 'approved') {
            if (request()->expectsJson()) {
                return response()->json(['ok' => true, 'already' => true]);
            }

            return redirect()
                ->route('admin.correction.list')
                ->with('message', 'すでに承認済みです');
        }

        try {
            DB::transaction(function () use ($changeRequest) {
                $req = AttendanceChangeRequest::query()
                    ->whereKey($changeRequest->id)
                    ->where('status', 'pending')
                    ->lockForUpdate()
                    ->firstOrFail();

                $attendance = Attendance::findOrFail($req->attendance_id);
                $attendance->load('breaks');

                $field = $req->target_field;
                $after = $req->after_value;

                if (in_array($field, ['clock_in_at', 'clock_out_at'], true)) {
                    $attendance->{$field} = $this->parseToDateTimeOrFail($attendance, $after);
                    $attendance->save();

                    $req->update([
                        'status'      => 'approved',
                        'approved_at' => now(),
                    ]);

                    return;
                }

                if (preg_match('/^break_(in|out)_at_(\d+)$/', $field, $m)) {
                    $io  = $m[1];
                    $idx = (int) $m[2];
                    $col = $io === 'in' ? 'break_in_at' : 'break_out_at';

                    $afterDt = $this->parseToDateTimeOrFail($attendance, $after);

                    $attendance->load('breaks');
                    $breaks = $attendance->breaks->sortBy('id')->values();
                    $target = $breaks->get($idx - 1);

                    if (! $target) {
                        $breakIn  = null;
                        $breakOut = null;

                        if ($col === 'break_in_at') {
                            $breakIn = $afterDt;
                        } else {
                            $breakIn  = $attendance->clock_in_at ?? $afterDt;
                            $breakOut = $afterDt;
                        }

                        $attendance->breaks()->create([
                            'break_in_at'  => $breakIn,
                            'break_out_at' => $breakOut,
                        ]);
                    } else {
                        $target->{$col} = $afterDt;

                        if (! $target->break_in_at) {
                            $target->break_in_at = $attendance->clock_in_at ?? $afterDt;
                        }

                        $target->save();
                    }

                    $req->update([
                        'status'      => 'approved',
                        'approved_at' => now(),
                    ]);

                    return;
                }

                throw new \RuntimeException("未対応のtarget_fieldです: {$field}");
            });
        } catch (\Throwable $e) {
            if (request()->expectsJson()) {
                return response()->json(
                    [
                        'ok'    => false,
                        'error' => $e->getMessage(),
                    ],
                    422
                );
            }

            return redirect()
                ->route('admin.correction.approve.show', ['changeRequest' => $changeRequest->id])
                ->with('error', $e->getMessage());
        }

        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'status' => 'approved']);
        }

        return redirect()
            ->route('admin.correction.list', ['tab' => 'pending'])
            ->with('message', '承認しました');
    }

    private function parseToDateTimeOrFail(Attendance $attendance, ?string $value): ?Carbon
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{1,2}:\d{2}$/', $value)) {
            $workDate = $attendance->work_date instanceof Carbon
                ? $attendance->work_date->toDateString()
                : (string) $attendance->work_date;

            return Carbon::createFromFormat('Y-m-d H:i', $workDate . ' ' . $value);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{1,2}:\d{2}:\d{2}$/', $value)) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $value);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{1,2}:\d{2}$/', $value)) {
            return Carbon::createFromFormat('Y-m-d H:i', $value);
        }

        throw new \InvalidArgumentException("承認できません：日時形式が不正です（{$value}）");
    }
}
