<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in'     => ['nullable', 'date_format:H:i'],
            'clock_out'    => ['nullable', 'date_format:H:i'],
            'break1_start' => ['nullable', 'date_format:H:i'],
            'break1_end'   => ['nullable', 'date_format:H:i'],
            'break2_start' => ['nullable', 'date_format:H:i'],
            'break2_end'   => ['nullable', 'date_format:H:i'],
            'note'         => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $attendance = $this->route('attendance');
            $workDate = $attendance?->work_date;

            if (! $workDate) {
                return;
            }

            $ci = $this->timeToCarbon($workDate, $this->input('clock_in'));
            $co = $this->timeToCarbon($workDate, $this->input('clock_out'));

            if ($ci && $co && $ci->gt($co)) {
                $v->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
                $v->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $this->validateBreak($v, $workDate, $ci, $co, 'break1_start', 'break1_end');
            $this->validateBreak($v, $workDate, $ci, $co, 'break2_start', 'break2_end');
        });
    }

    private function validateBreak($v, string $workDate, ?Carbon $ci, ?Carbon $co, string $startKey, string $endKey): void
    {
        $bs = $this->timeToCarbon($workDate, $this->input($startKey));
        $be = $this->timeToCarbon($workDate, $this->input($endKey));

        if ($bs && $ci && $bs->lt($ci)) {
            $v->errors()->add($startKey, '休憩時間が不適切な値です');
        }

        if ($bs && $co && $bs->gt($co)) {
            $v->errors()->add($startKey, '休憩時間が不適切な値です');
        }

        if ($be && $co && $be->gt($co)) {
            $v->errors()->add($endKey, '休憩時間もしくは退勤時間が不適切な値です');
        }

        if ($bs && $be && $be->lt($bs)) {
            $v->errors()->add($endKey, '休憩時間が不適切な値です');
        }
    }

    private function timeToCarbon(string $workDate, ?string $hm): ?Carbon
    {
        if (! $hm) {
            return null;
        }

        try {
            return Carbon::parse($workDate . ' ' . $hm);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
