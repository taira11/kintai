<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\AttendanceChangeRequest;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class AdminCorrectionRequestFlowTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    private function normalUser(array $override = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'user',
            'email_verified_at' => now(),
        ], $override));
    }

    private function seedAttendance(
        User $user,
        string $date,
        string $clockIn = '09:00',
        string $clockOut = '18:00'
    ): Attendance {
        return Attendance::create([
            'user_id' => $user->id,
            'work_date' => $date,
            'clock_in_at' => Carbon::parse("$date $clockIn:00"),
            'clock_out_at' => Carbon::parse("$date $clockOut:00"),
            'note' => '初期備考',
        ]);
    }

    private function seedBreak(
        Attendance $attendance,
        string $date,
        string $in,
        string $out
    ): BreakTime {
        return BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_in_at' => Carbon::parse("$date $in:00"),
            'break_out_at' => Carbon::parse("$date $out:00"),
        ]);
    }

    private function createChangeRequest(
        Attendance $attendance,
        User $requestedBy,
        string $status,
        string $targetField,
        string $afterDatetime,
        ?string $beforeDatetime = null,
        string $reason = '電車遅延のため'
    ): AttendanceChangeRequest {
        return AttendanceChangeRequest::create([
            'attendance_id' => $attendance->id,
            'requested_by' => $requestedBy->id,
            'status' => $status,
            'target_field' => $targetField,
            'before_value' => $beforeDatetime ? Carbon::parse($beforeDatetime) : null,
            'after_value' => Carbon::parse($afterDatetime),
            'reason' => $reason,
            'approved_at' => $status === 'approved' ? now() : null,
        ]);
    }

    /** @test */
    public function admin_can_see_all_pending_requests_in_pending_tab()
    {
        Carbon::setTestNow(Carbon::parse('2026-02-22 12:00:00'));

        $admin = $this->adminUser();

        $u1 = $this->normalUser(['name' => '一般ユーザーA']);
        $u2 = $this->normalUser(['name' => '一般ユーザーB']);

        $a1 = $this->seedAttendance($u1, '2026-02-22', '09:00', '18:00');
        $a2 = $this->seedAttendance($u2, '2026-02-22', '10:00', '19:00');

        $this->createChangeRequest($a1, $u1, 'pending', 'clock_in_at', '2026-02-22 09:30:00', '2026-02-22 09:00:00', '電車遅延A');
        $this->createChangeRequest($a2, $u2, 'pending', 'clock_out_at', '2026-02-22 19:30:00', '2026-02-22 19:00:00', '電車遅延B');
        $this->createChangeRequest($a1, $u1, 'approved', 'clock_out_at', '2026-02-22 18:30:00', '2026-02-22 18:00:00', '承認済みは出ない');

        $res = $this->actingAs($admin)->get(
            route('admin.correction.list', ['tab' => 'pending'])
        );

        $res->assertOk();
        $res->assertSee('一般ユーザーA');
        $res->assertSee('電車遅延A');
        $res->assertSee('一般ユーザーB');
        $res->assertSee('電車遅延B');
        $res->assertDontSee('承認済みは出ない');
    }

    /** @test */
    public function admin_can_see_all_approved_requests_in_approved_tab()
    {
        Carbon::setTestNow(Carbon::parse('2026-02-22 12:00:00'));

        $admin = $this->adminUser();
        $u1 = $this->normalUser(['name' => '一般ユーザーA']);

        $a1 = $this->seedAttendance($u1, '2026-02-22', '09:00', '18:00');

        $this->createChangeRequest($a1, $u1, 'pending', 'clock_in_at', '2026-02-22 09:30:00', '2026-02-22 09:00:00', 'pendingは出ない');
        $this->createChangeRequest($a1, $u1, 'approved', 'clock_out_at', '2026-02-22 18:30:00', '2026-02-22 18:00:00', '承認済み表示OK');

        $res = $this->actingAs($admin)->get(
            route('admin.correction.list', ['tab' => 'approved'])
        );

        $res->assertOk();
        $res->assertSee('一般ユーザーA');
        $res->assertSee('承認済み表示OK');
        $res->assertDontSee('pendingは出ない');
    }

    /** @test */
    public function admin_can_view_request_detail_and_it_shows_correct_info()
    {
        Carbon::setTestNow(Carbon::parse('2026-02-22 12:00:00'));

        $admin = $this->adminUser();
        $user = $this->normalUser(['name' => '一般ユーザーA']);

        $attendance = $this->seedAttendance($user, '2026-02-22', '09:00', '18:00');
        $this->seedBreak($attendance, '2026-02-22', '12:00', '13:00');

        $req = $this->createChangeRequest(
            $attendance,
            $user,
            'pending',
            'clock_in_at',
            '2026-02-22 09:30:00',
            '2026-02-22 09:00:00',
            '電車遅延のため'
        );

        $res = $this->actingAs($admin)->get(
            route('admin.correction.approve.show', ['changeRequest' => $req->id])
        );

        $res->assertOk();
        $res->assertSee('一般ユーザーA');
        $res->assertSee('2026年');
        $res->assertSee('2月22日');
        $res->assertSee('09:00');
        $res->assertSee('18:00');
        $res->assertSee('12:00');
        $res->assertSee('13:00');
    }

    /** @test */
    public function admin_can_approve_request_and_attendance_is_updated()
    {
        Carbon::setTestNow(Carbon::parse('2026-02-22 12:00:00'));

        $admin = $this->adminUser();
        $user = $this->normalUser(['name' => '一般ユーザーA']);

        $attendance = $this->seedAttendance($user, '2026-02-22', '09:00', '18:00');
        $this->seedBreak($attendance, '2026-02-22', '12:00', '13:00');

        $req = $this->createChangeRequest(
            $attendance,
            $user,
            'pending',
            'clock_in_at',
            '2026-02-22 09:30:00',
            '2026-02-22 09:00:00',
            '電車遅延のため'
        );

        $res = $this->actingAs($admin)
            ->withHeader('Accept', 'application/json')
            ->post(route('admin.correction.approve', ['changeRequest' => $req->id]));

        $res->assertOk();

        $this->assertDatabaseHas('attendance_change_requests', [
            'id' => $req->id,
            'status' => 'approved',
        ]);

        $attendance->refresh();
        $this->assertSame('09:30', optional($attendance->clock_in_at)->format('H:i'));
    }
}
