<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CurrentDateTimeDisplayTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function current_datetime_is_displayed_in_the_same_format_as_ui()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 21, 10, 30));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertOk();

        $now = Carbon::now();

        $dateLabel = sprintf(
            '%s(%s)',
            $now->format('Y年n月j日'),
            ['日', '月', '火', '水', '木', '金', '土'][$now->dayOfWeek]
        );

        $timeLabel = $now->format('H:i');

        $response
            ->assertSee($dateLabel)
            ->assertSee($timeLabel);
    }
}
