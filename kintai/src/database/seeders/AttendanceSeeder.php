<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->get();

        foreach ($users as $user) {
            for ($i = 1; $i <= 5; $i++) {

                $date = Carbon::now()->startOfMonth()->addDays($i);

                $attendance = Attendance::create([
                    'user_id'      => $user->id,
                    'work_date'    => $date->toDateString(),
                    'clock_in_at'  => Carbon::parse($date->toDateString().' 09:00:00'),
                    'clock_out_at' => Carbon::parse($date->toDateString().' 18:00:00'),
                    'note'         => 'ダミーデータ',
                ]);

                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_in_at'   => Carbon::parse($date->toDateString().' 12:00:00'),
                    'break_out_at'  => Carbon::parse($date->toDateString().' 13:00:00'),
                ]);

                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_in_at'   => Carbon::parse($date->toDateString().' 15:00:00'),
                    'break_out_at'  => Carbon::parse($date->toDateString().' 15:15:00'),
                ]);
            }
        }
    }
}
