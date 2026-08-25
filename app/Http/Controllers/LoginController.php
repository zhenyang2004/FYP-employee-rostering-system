<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\employee;

class LoginController extends Controller
{
    public function login(Request $request){

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ]);

        $loginCredentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        $remember = $request->has('remember');

        if (Auth::attempt($loginCredentials, $remember)) {

            $user = Auth::user();

            if ($user->status == 'Inactive') {

                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/userlogin')->withErrors(['email' => 'Your account is inactive. Please contact the administrator.']);
            }

            if (optional(auth()->user()->employee)->role == 'Admin') {
                return redirect()->route('employeelist');
            }

                 
            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        return back()->withInput()->withErrors([
            'email' => 'Invalid email or password.',
        ]);

    }

    public function logout(Request $request) {
        
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/userlogin');
    }
}
