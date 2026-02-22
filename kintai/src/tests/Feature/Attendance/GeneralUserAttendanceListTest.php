<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GeneralUserAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    private function loginVerifiedUser(): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        return $user;
    }

    private function clockInOutAt(Carbon $clockIn, Carbon $clockOut): void
    {
        Carbon::setTestNow($clockIn);
        $this->post('/attendance', ['action' => 'clock_in']);

        Carbon::setTestNow($clockOut);
        $this->post('/attendance', ['action' => 'clock_out']);
    }

    /** @test */
    public function all_of_my_attendance_records_are_displayed_on_the_list_page()
    {
        $user = $this->loginVerifiedUser();

        $this->clockInOutAt(
            Carbon::create(2026, 2, 5, 9, 1, 0),
            Carbon::create(2026, 2, 5, 18, 2, 0),
        );

        $this->clockInOutAt(
            Carbon::create(2026, 2, 20, 10, 3, 0),
            Carbon::create(2026, 2, 20, 19, 4, 0),
        );

        $other = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($other);

        $this->clockInOutAt(
            Carbon::create(2026, 2, 12, 7, 7, 0),
            Carbon::create(2026, 2, 12, 8, 8, 0),
        );

        $this->actingAs($user);

        $res = $this->get('/attendance/list?month=2026-02');

        $res->assertOk();
        $res->assertSee('09:01');
        $res->assertSee('18:02');
        $res->assertSee('10:03');
        $res->assertSee('19:04');
        $res->assertDontSee('07:07');
        $res->assertDontSee('08:08');
    }

    /** @test */
    public function current_month_is_displayed_when_opening_the_list_page()
    {
        $this->loginVerifiedUser();

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 10, 0, 0));

        $res = $this->get('/attendance/list');

        $res->assertOk();
        $res->assertSee('2026/02');
    }

    /** @test */
    public function previous_month_records_are_displayed_when_clicking_previous_month()
    {
        $this->loginVerifiedUser();

        $this->clockInOutAt(
            Carbon::create(2026, 1, 10, 9, 11, 0),
            Carbon::create(2026, 1, 10, 18, 12, 0),
        );

        $res = $this->get('/attendance/list?month=2026-01');

        $res->assertOk();
        $res->assertSee('2026/01');
        $res->assertSee('09:11');
        $res->assertSee('18:12');
    }

    /** @test */
    public function next_month_records_are_displayed_when_clicking_next_month()
    {
        $this->loginVerifiedUser();

        $this->clockInOutAt(
            Carbon::create(2026, 3, 3, 8, 21, 0),
            Carbon::create(2026, 3, 3, 17, 22, 0),
        );

        $res = $this->get('/attendance/list?month=2026-03');

        $res->assertOk();
        $res->assertSee('2026/03');
        $res->assertSee('08:21');
        $res->assertSee('17:22');
    }

    /** @test */
    public function it_navigates_to_attendance_detail_page_when_clicking_detail_link()
    {
        $this->loginVerifiedUser();

        $this->clockInOutAt(
            Carbon::create(2026, 2, 21, 9, 0, 0),
            Carbon::create(2026, 2, 21, 18, 0, 0),
        );

        $res = $this->get('/attendance/list?month=2026-02');

        $res->assertOk();
        $res->assertSee('詳細');
    }
}
