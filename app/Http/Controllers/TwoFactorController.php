<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TwoFactorController extends Controller
{
    public function showChallenge()
    {
        abort_unless(session()->has('2fa_user_id'), 403);
        return Inertia::render('Auth/TwoFactor');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $user = User::findOrFail(session('2fa_user_id'));

        if (!$user->verifyTwoFactorCode($request->code)) {
            return back()->withErrors(['code' => 'Codigo invalido o expirado.']);
        }

        $user->clearTwoFactorCode();
        session()->forget('2fa_user_id');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function enable(Request $request)
    {
        $request->user()->update(['two_factor_enabled' => true]);
        return back()->with('success', '2FA activado correctamente.');
    }

    public function resend(Request $request)
    {
        abort_unless(session()->has('2fa_user_id'), 403);

        $user = User::findOrFail(session('2fa_user_id'));
        $code = $user->generateTwoFactorCode();
        $user->notify(new TwoFactorCodeNotification($code));

        return back()->with('success', 'Codigo reenviado correctamente.');
    }
}