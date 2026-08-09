<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function showResetPasswordForm(Request $request, $token) {

        return view('resetpassword', ['token' => $token, 'email' => $request->email]);

    }

    public function resetPassword(Request $request) {

        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }     
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect('/userlogin')->with('success', 'Password reset successfully! Please login with your new password.');
        }

        return back()->withErrors([
            'email' => 'Invalid or expired reset link.'
        ]);
    }
}
