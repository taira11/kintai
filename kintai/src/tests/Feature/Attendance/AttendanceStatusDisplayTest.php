<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceStatusDisplayTest extends TestCase
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

    /** @test */
    public function status_is_working_outside_when_user_has_no_stamp_today()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 21, 10, 0, 0));

        $this->loginVerifiedUser();

        $response = $this->get('/attendance');

        $response->assertOk();
        $response->assertSee('勤務外');
    }

    /** @test */
    public function status_is_working_when_user_clocked_in()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 21, 10, 0, 0));

        $this->loginVerifiedUser();

        $this->post('/attendance', [
            'action' => 'clock_in',
        ]);

        $response = $this->get('/attendance');

        $response->assertOk();
        $response->assertSee('出勤中');
    }

    /** @test */
    public function status_is_on_break_when_user_started_break()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 21, 10, 0, 0));

        $this->loginVerifiedUser();

        $this->post('/attendance', ['action' => 'clock_in']);
        $this->post('/attendance', ['action' => 'break_in']);

        $response = $this->get('/attendance');

        $response->assertOk();
        $response->assertSee('休憩中');
    }

    /** @test */
    public function status_is_clocked_out_when_user_clocked_out()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 21, 10, 0, 0));

        $this->loginVerifiedUser();

        $this->post('/attendance', ['action' => 'clock_in']);
        $this->post('/attendance', ['action' => 'clock_out']);

        $response = $this->get('/attendance');

        $response->assertOk();
        $response->assertSee('退勤済');
    }
}
