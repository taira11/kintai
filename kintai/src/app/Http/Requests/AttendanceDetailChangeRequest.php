<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceDetailChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in'     => ['nullable', 'date_format:H:i'],
            'clock_out'    => ['nullable', 'date_format:H:i'],
            'breaks'       => ['array'],
            'breaks.*.in'  => ['nullable', 'date_format:H:i'],
            'breaks.*.out' => ['nullable', 'date_format:H:i'],
            'note'         => ['required', 'string', 'max:1000'],
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
            $clockIn  = $this->input('clock_in');
            $clockOut = $this->input('clock_out');
            $breaks   = $this->input('breaks', []);

            $ci = $this->hmToCarbon($clockIn);
            $co = $this->hmToCarbon($clockOut);

            if ($ci && $co && $ci->gte($co)) {
                $msg = '出勤時間もしくは退勤時間が不適切な値です';
                $v->errors()->add('clock_in', $msg);
                $v->errors()->add('clock_out', $msg);
            }

            foreach ($breaks as $i => $b) {
                $bin  = $b['in']  ?? null;
                $bout = $b['out'] ?? null;

                if (! $bin && ! $bout) {
                    continue;
                }

                $bs = $this->hmToCarbon($bin);
                $be = $this->hmToCarbon($bout);

                if ($bs && $ci && $bs->lt($ci)) {
                    $v->errors()->add("breaks.$i.in", '休憩時間が不適切な値です');
                }

                if ($bs && $co && $bs->gt($co)) {
                    $v->errors()->add("breaks.$i.in", '休憩時間が不適切な値です');
                }

                if ($be && $co && $be->gt($co)) {
                    $v->errors()->add("breaks.$i.out", '休憩時間もしくは退勤時間が不適切な値です');
                }

                if ($bs && $be && $bs->gte($be)) {
                    $v->errors()->add("breaks.$i.in", '休憩時間が不適切な値です');
                }
            }
        });
    }

    private function hmToCarbon(?string $hm): ?Carbon
    {
        $hm = trim((string) $hm);

        if ($hm === '') {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d H:i', '2000-01-01 ' . $hm);
    }
}
