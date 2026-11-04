<?php
use Illuminate\Support\Facades\Auth;

if (!function_exists('redirectIfLoggedIn')) {
    function redirectIfLoggedIn()
    {
        if (Auth::check()) {
            return redirect()->route('admindashboard.get');
        }

        if (Auth::guard('student')->check()) {
            return redirect()->route('student.dashboard');
        }

        if (Auth::guard('institute')->check()) {
            return redirect()->route('institute.dashboard');
        }

        return null;
    }
}
