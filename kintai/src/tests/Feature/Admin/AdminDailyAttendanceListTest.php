<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class AdminDailyAttendanceListTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function admin_can_view_today_attendance_list()
    {
        Carbon::setTestNow(Carbon::parse('2026-02-22 12:00:00'));

        $admin = $this->adminUser();

        $user1 = User::factory()->create(['name' => 'user1']);
        $user2 = User::factory()->create(['name' => 'user2']);

        Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2026-02-22',
            'clock_in_at' => '2026-02-22 09:00:00',
            'clock_out_at' => '2026-02-22 18:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => '2026-02-22',
            'clock_in_at' => '2026-02-22 10:00:00',
            'clock_out_at' => '2026-02-22 19:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list'));

        $response->assertOk();

        $response->assertSee('2026');
        $response->assertSee('22');

        $response->assertSee('user1');
        $response->assertSee('user2');

        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function admin_can_view_previous_day()
    {
        $admin = $this->adminUser();

        $user = User::factory()->create(['name' => 'prevUser']);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-21',
            'clock_in_at' => '2026-02-21 09:00:00',
            'clock_out_at' => '2026-02-21 18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(
            route('admin.attendance.list', ['date' => '2026-02-21'])
        );

        $response->assertOk();
        $response->assertSee('prevUser');
    }

    /** @test */
    public function admin_can_view_next_day()
    {
        $admin = $this->adminUser();

        $user = User::factory()->create(['name' => 'nextUser']);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-23',
            'clock_in_at' => '2026-02-23 09:00:00',
            'clock_out_at' => '2026-02-23 18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(
            route('admin.attendance.list', ['date' => '2026-02-23'])
        );

        $response->assertOk();
        $response->assertSee('nextUser');
    }
}
