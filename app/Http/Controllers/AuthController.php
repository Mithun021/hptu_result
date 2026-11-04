<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        // if (Auth::check()) {
        //     return redirect()->route('admindashboard.get');
        // }
        if ($redirect = redirectIfLoggedIn()) {
            return $redirect;
        }
        return view('backend.login');
    }

    public function studentLoginForm(){
        if ($redirect = redirectIfLoggedIn()) {
            return $redirect;
        }
        return view('userlogin');
    }

    public function instituteLoginForm(){
        if ($redirect = redirectIfLoggedIn()) {
            return $redirect;
        }
        return view('institutelogin');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $remember = $request->has('remember');
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(url('/'));
        }

        return back()->withErrors(['email' => 'Invalid email or password.']);
    }

    public function studentLogin(Request $request)
    {
        $request->validate([
            'roll_no' => 'required',   // here roll_no field holds roll no
            'password' => 'required|min:6',
        ]);

        $credentials = [
            'university_roll_no' => $request->roll_no,
            'password' => $request->password,
        ];

        if (Auth::guard('student')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('student.dashboard');
        }

        return back()->withErrors(['roll_no' => 'Invalid Roll No or Password']);
    }

    public function instituteLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::guard('institute')->attempt($request->only('email','password'))) {
            $request->session()->regenerate();
            return redirect()->route('institute.dashboard');
        }

        return back()->withErrors(['email' => 'Invalid Email or Password']);
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function studentLogout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        return redirect()->route('student.login');
    }

    public function instituteLogout(Request $request)
    {
        Auth::guard('institute')->logout();
        $request->session()->invalidate();
        return redirect()->route('institute.login');
    }

}
