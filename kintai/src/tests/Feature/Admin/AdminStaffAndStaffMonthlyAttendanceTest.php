<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class AdminStaffAndStaffMonthlyAttendanceTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    private function normalUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'user',
            'email_verified_at' => now(),
        ], $attrs));
    }

    private function seedAttendance(
        User $user,
        string $date,
        string $in = '09:00',
        string $out = '18:00'
    ): Attendance {
        return Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $date,
            'clock_in_at' => Carbon::parse("$date $in:00"),
            'clock_out_at' => Carbon::parse("$date $out:00"),
            'note' => 'seed',
        ]);
    }

    private function seedBreak(
        Attendance $attendance,
        string $date,
        string $bin,
        string $bout
    ): void {
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in_at' => Carbon::parse("$date $bin:00"),
            'break_out_at' => Carbon::parse("$date $bout:00"),
        ]);
    }

    /** @test */
    public function admin_can_view_staff_list_and_see_all_general_users_name_and_email()
    {
        $admin = $this->adminUser();

        $u1 = $this->normalUser([
            'name' => '山田 太郎',
            'email' => 'taro@example.com',
        ]);

        $u2 = $this->normalUser([
            'name' => '佐藤 花子',
            'email' => 'hanako@example.com',
        ]);

        $res = $this->actingAs($admin)->get(route('admin.staff.list'));
        $res->assertOk();

        $res->assertSee($u1->name);
        $res->assertSee($u1->email);

        $res->assertSee($u2->name);
        $res->assertSee($u2->email);

        $res->assertDontSee($admin->email);
    }

    /** @test */
    public function admin_can_view_selected_users_monthly_attendance_list_and_it_shows_correct_data()
    {
        Carbon::setTestNow(Carbon::parse('2026-02-22 12:00:00'));

        $admin = $this->adminUser();
        $user  = $this->normalUser(['name' => '田中 一郎']);

        $a1 = $this->seedAttendance($user, '2026-02-10', '10:00', '19:00');
        $this->seedBreak($a1, '2026-02-10', '12:00', '13:00');

        $this->seedAttendance($user, '2026-02-11', '09:30', '18:30');

        $this->seedAttendance($user, '2026-01-15', '08:00', '17:00');

        $res = $this->actingAs($admin)->get(route('admin.attendance.staff', [
            'user'  => $user->id,
            'month' => '2026-02',
        ]));
        $res->assertOk();

        $res->assertSee('10:00');
        $res->assertSee('19:00');
        $res->assertSee('09:30');
        $res->assertSee('18:30');

        $res->assertDontSee('08:00');
        $res->assertDontSee('17:00');
    }

    /** @test */
    public function admin_can_view_previous_month_on_staff_monthly_attendance_list()
    {
        Carbon::setTestNow(Carbon::parse('2026-02-22 12:00:00'));

        $admin = $this->adminUser();
        $user  = $this->normalUser();

        $this->seedAttendance($user, '2026-01-20', '08:00', '17:00');
        $this->seedAttendance($user, '2026-02-20', '09:00', '18:00');

        $res = $this->actingAs($admin)->get(route('admin.attendance.staff', [
            'user'  => $user->id,
            'month' => '2026-01',
        ]));
        $res->assertOk();

        $res->assertSee('08:00');
        $res->assertSee('17:00');

        $res->assertDontSee('09:00');
        $res->assertDontSee('18:00');
    }

    /** @test */
    public function admin_can_view_next_month_on_staff_monthly_attendance_list()
    {
        Carbon::setTestNow(Carbon::parse('2026-02-22 12:00:00'));

        $admin = $this->adminUser();
        $user  = $this->normalUser();

        $this->seedAttendance($user, '2026-03-05', '11:00', '20:00');
        $this->seedAttendance($user, '2026-02-05', '09:00', '18:00');

        $res = $this->actingAs($admin)->get(route('admin.attendance.staff', [
            'user'  => $user->id,
            'month' => '2026-03',
        ]));
        $res->assertOk();

        $res->assertSee('11:00');
        $res->assertSee('20:00');

        $res->assertDontSee('09:00');
        $res->assertDontSee('18:00');
    }

    /** @test */
    public function admin_can_navigate_to_attendance_detail_from_staff_monthly_list()
    {
        Carbon::setTestNow(Carbon::parse('2026-02-22 12:00:00'));

        $admin = $this->adminUser();
        $user  = $this->normalUser();

        $attendance = $this->seedAttendance($user, '2026-02-12', '09:00', '18:00');

        $res = $this->actingAs($admin)->get(route('admin.attendance.staff', [
            'user' => $user->id,
        ]));
        $res->assertOk();

        $detailUrl = route('admin.attendance.show', ['attendance' => $attendance->id]);
        $res->assertSee($detailUrl);

        $detail = $this->actingAs($admin)->get($detailUrl);
        $detail->assertOk();
        $detail->assertSee('09:00');
        $detail->assertSee('18:00');
    }
}
