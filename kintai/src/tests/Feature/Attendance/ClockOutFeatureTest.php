<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ClockOutFeatureTest extends TestCase
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

        return sprintf(
            '%s(%s)',
            $today->format('m/d'),
            ['日', '月', '火', '水', '木', '金', '土'][$today->dayOfWeek]
        );
    }

    /** @test */
    public function clock_in_button_is_visible_and_status_changes_to_working_after_clock_in()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 21, 9));

        $this->loginVerifiedUser();

        $this->get('/attendance')
            ->assertOk()
            ->assertSee('出勤');

        $this->post('/attendance', ['action' => 'clock_in']);

        $this->get('/attendance')
            ->assertOk()
            ->assertSee('出勤中');
    }

    /** @test */
    public function clock_in_button_is_not_visible_after_clocking_out()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 21, 9));

        $this->loginVerifiedUser();

        $this->post('/attendance', ['action' => 'clock_in']);
        $this->post('/attendance', ['action' => 'clock_out']);

        $this->get('/attendance')
            ->assertOk()
            ->assertSee('退勤済')
            ->assertDontSee('出勤');
    }

    /** @test */
    public function clock_in_time_is_recorded_correctly_on_attendance_list_page()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 21, 9, 15));

        $this->loginVerifiedUser();

        $this->post('/attendance', ['action' => 'clock_in']);

        $this->get('/attendance/list')
            ->assertOk()
            ->assertSee($this->dateLabelFromTestNow())
            ->assertSee(Carbon::now()->format('H:i'));
    }
}
