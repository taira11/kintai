<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GeneralUserAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function loginVerifiedUser(): User
    {
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        return $user;
    }

    private function createAttendanceWithBreak(User $user): Attendance
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 21, 9, 0, 0));
        $this->actingAs($user)->post('/attendance', ['action' => 'clock_in']);

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 12, 0, 0));
        $this->actingAs($user)->post('/attendance', ['action' => 'break_in']);

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 12, 30, 0));
        $this->actingAs($user)->post('/attendance', ['action' => 'break_out']);

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 18, 0, 0));
        $this->actingAs($user)->post('/attendance', ['action' => 'clock_out']);

        return Attendance::where('user_id', $user->id)
            ->where('work_date', '2026-02-21')
            ->firstOrFail();
    }

    /** @test */
    public function name_on_detail_page_matches_logged_in_user_name()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $res = $this->get("/attendance/detail/{$attendance->id}");
        $res->assertOk();

        $res->assertViewHas('userName', $user->name);
        $res->assertSee($user->name);
    }

    /** @test */
    public function date_on_detail_page_matches_selected_date()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $res = $this->get("/attendance/detail/{$attendance->id}");
        $res->assertOk();

        $res->assertViewHas('attendance', function ($a) {
            $got = $a->work_date instanceof Carbon
                ? $a->work_date->toDateString()
                : (string) $a->work_date;

            return $got === '2026-02-21';
        });
    }

    /** @test */
    public function clock_in_and_clock_out_times_on_detail_page_match_user_stamps()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $res = $this->get("/attendance/detail/{$attendance->id}");
        $res->assertOk();

        $res->assertViewHas('attendance', function ($a) {
            return $a->clock_in_at?->format('H:i') === '09:00'
                && $a->clock_out_at?->format('H:i') === '18:00';
        });

        $res->assertSee('09:00');
        $res->assertSee('18:00');
    }

    /** @test */
    public function break_times_on_detail_page_match_user_stamps()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->createAttendanceWithBreak($user);

        $res = $this->get("/attendance/detail/{$attendance->id}");
        $res->assertOk();

        $res->assertViewHas('breakRows', function ($breakRows) {
            return isset($breakRows[0])
                && ($breakRows[0]['in'] ?? null) === '12:00'
                && ($breakRows[0]['out'] ?? null) === '12:30';
        });

        $res->assertSee('12:00');
        $res->assertSee('12:30');
    }
}
