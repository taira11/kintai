<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminAttendanceDetailUpdateTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    private function adminUser(): \App\Models\User
    {
        return \App\Models\User::factory()->create([
            'role' => 'admin',
        ]);
    }

    private function normalUser(): \App\Models\User
    {
        return \App\Models\User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
    }

    private function seedAttendanceWithBreaks(
        \App\Models\User $user,
        string $date = '2026-02-22'
    ): \App\Models\Attendance {
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $date,
            'clock_in_at' => \Carbon\Carbon::parse("$date 10:58:00"),
            'clock_out_at' => \Carbon\Carbon::parse("$date 22:58:00"),
            'note' => 'test',
        ]);

        \App\Models\BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in_at' => \Carbon\Carbon::parse("$date 11:58:00"),
            'break_out_at' => \Carbon\Carbon::parse("$date 12:58:00"),
        ]);

        return $attendance;
    }

    /** @test */
    public function admin_can_view_attendance_detail_and_values_match_selected_attendance()
    {
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-02-22 12:00:00'));

        $admin = $this->adminUser();
        $user = $this->normalUser();
        $attendance = $this->seedAttendanceWithBreaks($user, '2026-02-22');

        $res = $this->actingAs($admin)->get(
            route('admin.attendance.show', ['attendance' => $attendance->id])
        );

        $res->assertOk();

        $res->assertSee($user->name);
        $res->assertSee('2026年');
        $res->assertSee('2月22日');

        $res->assertSee('10:58');
        $res->assertSee('22:58');

        $res->assertSee('11:58');
        $res->assertSee('12:58');

        $res->assertSee('test');
    }

    /** @test */
    public function it_shows_error_when_clock_in_is_after_clock_out()
    {
        $admin = $this->adminUser();
        $user = $this->normalUser();
        $attendance = $this->seedAttendanceWithBreaks($user, '2026-02-22');

        $res = $this->actingAs($admin)
            ->from(route('admin.attendance.show', ['attendance' => $attendance->id]))
            ->post(route('admin.attendance.update', ['attendance' => $attendance->id]), [
                'clock_in' => '18:00',
                'clock_out' => '09:00',
                'break1_start' => null,
                'break1_end' => null,
                'break2_start' => null,
                'break2_end' => null,
                'note' => '備考テスト',
            ]);

        $res->assertRedirect(route('admin.attendance.show', ['attendance' => $attendance->id]));

        $follow = $this->actingAs($admin)->get(
            route('admin.attendance.show', ['attendance' => $attendance->id])
        );

        $follow->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    /** @test */
    public function it_shows_error_when_break_start_is_after_clock_out()
    {
        $admin = $this->adminUser();
        $user = $this->normalUser();
        $attendance = $this->seedAttendanceWithBreaks($user, '2026-02-22');

        $res = $this->actingAs($admin)
            ->from(route('admin.attendance.show', ['attendance' => $attendance->id]))
            ->post(route('admin.attendance.update', ['attendance' => $attendance->id]), [
                'clock_in' => '09:00',
                'clock_out' => '17:00',
                'break1_start' => '18:00',
                'break1_end' => '18:30',
                'break2_start' => null,
                'break2_end' => null,
                'note' => '備考テスト',
            ]);

        $res->assertRedirect(route('admin.attendance.show', ['attendance' => $attendance->id]));

        $follow = $this->actingAs($admin)->get(
            route('admin.attendance.show', ['attendance' => $attendance->id])
        );

        $follow->assertSee('休憩時間が不適切な値です');
    }

    /** @test */
    public function it_shows_error_when_break_end_is_after_clock_out()
    {
        $admin = $this->adminUser();
        $user = $this->normalUser();
        $attendance = $this->seedAttendanceWithBreaks($user, '2026-02-22');

        $res = $this->actingAs($admin)
            ->from(route('admin.attendance.show', ['attendance' => $attendance->id]))
            ->post(route('admin.attendance.update', ['attendance' => $attendance->id]), [
                'clock_in' => '09:00',
                'clock_out' => '17:00',
                'break1_start' => '16:30',
                'break1_end' => '18:00',
                'break2_start' => null,
                'break2_end' => null,
                'note' => '備考テスト',
            ]);

        $res->assertRedirect(route('admin.attendance.show', ['attendance' => $attendance->id]));

        $follow = $this->actingAs($admin)->get(
            route('admin.attendance.show', ['attendance' => $attendance->id])
        );

        $follow->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    /** @test */
    public function it_shows_error_when_note_is_empty()
    {
        $admin = $this->adminUser();
        $user = $this->normalUser();
        $attendance = $this->seedAttendanceWithBreaks($user, '2026-02-22');

        $res = $this->actingAs($admin)
            ->from(route('admin.attendance.show', ['attendance' => $attendance->id]))
            ->post(route('admin.attendance.update', ['attendance' => $attendance->id]), [
                'clock_in' => '09:00',
                'clock_out' => '17:00',
                'break1_start' => '12:00',
                'break1_end' => '13:00',
                'break2_start' => null,
                'break2_end' => null,
                'note' => '',
            ]);

        $res->assertRedirect(route('admin.attendance.show', ['attendance' => $attendance->id]));

        $follow = $this->actingAs($admin)->get(
            route('admin.attendance.show', ['attendance' => $attendance->id])
        );

        $follow->assertSee('備考を記入してください');
    }
}
