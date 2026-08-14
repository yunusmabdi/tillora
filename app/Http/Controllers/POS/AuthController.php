<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('pos.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            if (auth()->user()->hasRole('Cashier')) {
                return redirect('/pos');
            }

            if (auth()->user()->hasRole('Admin')) {
                return redirect('/admin');
            }

            Auth::logout();

            return back()->withErrors([
                'email' => 'You are not allowed to access this system.'
            ]);
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.'
        ]);
    }

    public function demoCashier(Request $request)
    {
        $user = User::role('Cashier')->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'No Cashier account is available for demo access.'
            ]);
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect('/pos');
    }

    public function demoAdmin(Request $request)
    {
        $user = User::role('Admin')->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'No Admin account is available for demo access.'
            ]);
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect('/admin');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('pos.login');
    }
}