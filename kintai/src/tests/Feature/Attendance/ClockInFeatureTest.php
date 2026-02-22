<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ClockInFeatureTest extends TestCase
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

    private function dateLabelFromTestNow(): string
    {
        $today = Carbon::now();

        return $today->format('m/d') . '(' . ['日', '月', '火', '水', '木', '金', '土'][$today->dayOfWeek] . ')';
    }

    /** @test */
    public function clock_in_button_is_visible_and_status_changes_to_working_after_clock_in()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 21, 9, 0, 0));

        $this->loginVerifiedUser();

        $response = $this->get('/attendance');

        $response->assertOk();
        $response->assertSee('出勤');

        $this->post('/attendance', ['action' => 'clock_in']);

        $after = $this->get('/attendance');

        $after->assertOk();
        $after->assertSee('出勤中');
    }

    /** @test */
    public function clock_in_button_is_not_visible_after_clocking_out()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 21, 9, 0, 0));

        $this->loginVerifiedUser();

        $this->post('/attendance', ['action' => 'clock_in']);
        $this->post('/attendance', ['action' => 'clock_out']);

        $response = $this->get('/attendance');

        $response->assertOk();
        $response->assertSee('退勤済');
        $response->assertDontSee('出勤');
    }

    /** @test */
    public function clock_in_time_is_recorded_correctly_on_attendance_list_page()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 21, 9, 15, 0));

        $this->loginVerifiedUser();

        $this->post('/attendance', ['action' => 'clock_in']);

        $response = $this->get('/attendance/list');

        $response->assertOk();
        $response->assertSee($this->dateLabelFromTestNow());
        $response->assertSee(Carbon::now()->format('H:i'));
    }
}
