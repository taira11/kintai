<?php

namespace App\Http\Controllers;

use App\Models\AttendanceChangeRequest;
use Illuminate\Http\Request;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $tab = $request->query('tab', 'pending');

        if (! in_array($tab, ['pending', 'approved'], true)) {
            $tab = 'pending';
        }

        $query = AttendanceChangeRequest::query()
            ->where('requested_by', $user->id);

        if ($tab === 'pending') {
            $query->where('status', 'pending');
        } else {
            $query->where('status', 'approved');
        }

        $requests = $query
            ->with(['attendance', 'requester'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('stamp_correction_request.list', [
            'tab'      => $tab,
            'requests' => $requests,
        ]);
    }
}
