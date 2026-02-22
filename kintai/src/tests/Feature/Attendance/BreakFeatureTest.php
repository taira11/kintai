<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BreakFeatureTest extends TestCase
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
    public function break_in_button_is_visible_and_status_changes_to_on_break_after_break_in()
    {
        $this->loginVerifiedUser();

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 9, 0, 0));
        $this->post('/attendance', ['action' => 'clock_in']);

        $before = $this->get('/attendance');

        $before->assertOk();
        $before->assertSee('出勤中');
        $before->assertSee('休憩入');

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 12, 0, 0));
        $this->post('/attendance', ['action' => 'break_in']);

        $after = $this->get('/attendance');

        $after->assertOk();
        $after->assertSee('休憩中');
        $after->assertSee('休憩戻');
    }

    /** @test */
    public function break_can_be_taken_multiple_times_per_day_break_in_is_visible_again_after_break_out()
    {
        $this->loginVerifiedUser();

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 9, 0, 0));
        $this->post('/attendance', ['action' => 'clock_in']);

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 12, 0, 0));
        $this->post('/attendance', ['action' => 'break_in']);

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 12, 30, 0));
        $this->post('/attendance', ['action' => 'break_out']);

        $response = $this->get('/attendance');

        $response->assertOk();
        $response->assertSee('出勤中');
        $response->assertSee('休憩入');
    }

    /** @test */
    public function break_out_button_is_visible_and_status_changes_back_to_working_after_break_out()
    {
        $this->loginVerifiedUser();

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 9, 0, 0));
        $this->post('/attendance', ['action' => 'clock_in']);

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 12, 0, 0));
        $this->post('/attendance', ['action' => 'break_in']);

        $before = $this->get('/attendance');

        $before->assertOk();
        $before->assertSee('休憩中');
        $before->assertSee('休憩戻');

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 12, 30, 0));
        $this->post('/attendance', ['action' => 'break_out']);

        $after = $this->get('/attendance');

        $after->assertOk();
        $after->assertSee('出勤中');
        $after->assertSee('休憩入');
    }

    /** @test */
    public function break_out_can_be_done_multiple_times_per_day_break_out_is_visible_again_after_second_break_in()
    {
        $this->loginVerifiedUser();

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 9, 0, 0));
        $this->post('/attendance', ['action' => 'clock_in']);

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 12, 0, 0));
        $this->post('/attendance', ['action' => 'break_in']);

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 12, 30, 0));
        $this->post('/attendance', ['action' => 'break_out']);

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 15, 0, 0));
        $this->post('/attendance', ['action' => 'break_in']);

        $response = $this->get('/attendance');

        $response->assertOk();
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');
    }

    /** @test */
    public function break_time_is_recorded_correctly_on_attendance_list_page()
    {
        $this->loginVerifiedUser();

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 9, 0, 0));
        $this->post('/attendance', ['action' => 'clock_in']);

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 12, 0, 0));
        $this->post('/attendance', ['action' => 'break_in']);

        Carbon::setTestNow(Carbon::create(2026, 2, 21, 12, 30, 0));
        $this->post('/attendance', ['action' => 'break_out']);

        $res = $this->get('/attendance/list');

        $res->assertOk();
        $res->assertSee($this->dateLabelFromTestNow());
        $res->assertSee('0:30');
    }
}
