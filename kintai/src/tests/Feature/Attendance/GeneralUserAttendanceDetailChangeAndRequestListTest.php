<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceChangeRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GeneralUserAttendanceDetailChangeAndRequestListTest extends TestCase
{
    use RefreshDatabase;

    private function loginVerifiedUser(string $name = 'テスト太郎'): User
    {
        $user = User::factory()->create([
            'name' => $name,
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        $this->actingAs($user);

        return $user;
    }

    private function loginAdminUser(string $name = '管理者'): User
    {
        $admin = User::factory()->create([
            'name' => $name,
            'role' => 'admin',
        ]);

        $this->actingAs($admin, 'admin');

        return $admin;
    }

    private function seedAttendance(User $user): Attendance
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

    private function postChangeRequest(int $attendanceId, array $payload)
    {
        return $this->post("/attendance/detail/{$attendanceId}/request", $payload);
    }

    /** @test */
    public function it_shows_error_when_clock_in_is_after_clock_out()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->seedAttendance($user);

        $res = $this->postChangeRequest($attendance->id, [
            'clock_in' => '19:00',
            'clock_out' => '18:00',
            'breaks' => [],
            'note' => '修正理由',
        ]);

        $res->assertSessionHasErrors();
        $this->assertSame('出勤時間もしくは退勤時間が不適切な値です', session('errors')->first());
    }

    /** @test */
    public function it_shows_error_when_break_start_is_after_clock_out()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->seedAttendance($user);

        $res = $this->postChangeRequest($attendance->id, [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['in' => '19:00', 'out' => '19:30'],
            ],
            'note' => '修正理由',
        ]);

        $res->assertSessionHasErrors();
        $this->assertSame('休憩時間が不適切な値です', session('errors')->first());
    }

    /** @test */
    public function it_shows_error_when_break_end_is_after_clock_out()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->seedAttendance($user);

        $res = $this->postChangeRequest($attendance->id, [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['in' => '17:00', 'out' => '19:00'],
            ],
            'note' => '修正理由',
        ]);

        $res->assertSessionHasErrors();
        $this->assertSame('休憩時間もしくは退勤時間が不適切な値です', session('errors')->first());
    }

    /** @test */
    public function it_shows_error_when_note_is_empty()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->seedAttendance($user);

        $res = $this->postChangeRequest($attendance->id, [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['in' => '12:00', 'out' => '12:30'],
            ],
            'note' => '',
        ]);

        $res->assertSessionHasErrors();
        $this->assertSame('備考を記入してください', session('errors')->first());
    }

    /** @test */
    public function request_flow_pending_to_approved_is_visible_on_user_and_admin_and_detail_link_works()
    {
        $user = $this->loginVerifiedUser('テスト太郎');
        $attendance = $this->seedAttendance($user);

        $res = $this->postChangeRequest($attendance->id, [
            'clock_in' => '09:10',
            'clock_out' => '18:00',
            'breaks' => [
                ['in' => '12:00', 'out' => '12:30'],
            ],
            'note' => '電車遅延のため',
        ]);

        $res->assertRedirect();
        $res->assertSessionHas('success', '修正申請を送信しました。');

        $this->assertDatabaseHas('attendance_change_requests', [
            'attendance_id' => $attendance->id,
            'requested_by' => $user->id,
            'status' => 'pending',
            'target_field' => 'clock_in_at',
            'reason' => '電車遅延のため',
        ]);

        $changeRequest = AttendanceChangeRequest::where('attendance_id', $attendance->id)
            ->where('target_field', 'clock_in_at')
            ->latest('id')
            ->firstOrFail();

        $this->actingAs($user);

        $pendingList = $this->get('/stamp_correction_request/list?tab=pending');

        $pendingList->assertOk();
        $pendingList->assertSee('テスト太郎');
        $pendingList->assertSee('2026/02/21');
        $pendingList->assertSee('承認待ち');
        $pendingList->assertSee('詳細');
        $pendingList->assertDontSee('電車遅延のため');

        $html = $pendingList->getContent();
        preg_match('/href="([^"]*\/attendance\/detail\/\d+)"/u', $html, $m);

        $detailUrl = $m[1] ?? "/attendance/detail/{$attendance->id}";
        $detail = $this->get(html_entity_decode($detailUrl, ENT_QUOTES));

        $detail->assertOk();

        $this->loginAdminUser('管理者');

        $adminPending = $this->get('/admin/stamp_correction_request/list?tab=pending');

        $adminPending->assertOk();
        $adminPending->assertSee('テスト太郎');
        $adminPending->assertSee('2026/02/21');
        $adminPending->assertSee('電車遅延のため');
        $adminPending->assertSee('承認待ち');

        $approve = $this->post("/admin/stamp_correction_request/approve/{$changeRequest->id}");

        $approve->assertRedirect();

        $changeRequest->refresh();
        $this->assertSame('approved', $changeRequest->status);

        $attendance->refresh();
        $this->assertSame('09:10', Carbon::parse($attendance->clock_in_at)->format('H:i'));

        $adminApproved = $this->get('/admin/stamp_correction_request/list?tab=approved');

        $adminApproved->assertOk();
        $adminApproved->assertSee('テスト太郎');
        $adminApproved->assertSee('2026/02/21');
        $adminApproved->assertSee('電車遅延のため');
        $adminApproved->assertSee('承認済み');

        $this->actingAs($user);

        $pendingAfter = $this->get('/stamp_correction_request/list?tab=pending');

        $pendingAfter->assertOk();
        $pendingAfter->assertDontSee('電車遅延のため');

        $this->assertDatabaseMissing('attendance_change_requests', [
            'id' => $changeRequest->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('attendance_change_requests', [
            'id' => $changeRequest->id,
            'status' => 'approved',
        ]);

        $approvedAfter = $this->get('/stamp_correction_request/list?tab=approved');

        $approvedAfter->assertOk();
        $approvedAfter->assertSee('承認済み');
        $approvedAfter->assertSee('電車遅延のため');
    }
}
