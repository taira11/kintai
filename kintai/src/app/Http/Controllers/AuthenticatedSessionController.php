<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function store(LoginRequest $request)
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return back()
                ->withErrors(['email' => 'ログイン情報が登録されていません'])
                ->withInput();
        }

        if (! Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return redirect('/');
    }
}
