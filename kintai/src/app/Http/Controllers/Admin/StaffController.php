<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class StaffController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'user')
            ->orderBy('id')
            ->get(['id', 'name', 'email']);

        return view('admin.staff.list', compact('users'));
    }
}
