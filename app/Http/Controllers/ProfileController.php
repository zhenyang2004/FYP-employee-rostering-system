<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $breadcrumbs = [];
        
        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Profile',
            'url' => route('userprofile')
        ];

        $user = $this->getUserInfo();

        return view('userprofile', compact('breadcrumbs', 'user'));
    }

    private function getUserInfo() {

        return auth()->user();

    }


    public function editProfile() {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Profile',
            'url' => route('userprofile')
        ];

        $breadcrumbs[] = [
            'text' => 'Edit Profile',
            'url' => route('editprofile')
        ];

        $user = $this->getUserInfo();

        return view('editprofile', compact('breadcrumbs', 'user'));
    }


    public function updateProfile(Request $request) {

        /** @var User $user */
        $user = $this->getUserInfo();
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',   
            'employee_id' => 'required|string|max:50',
            'ic_number' => 'required|numeric|digits:12',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email,'. $user->id,
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $profilePicPath = $user->profile_pic;

        if ($request->remove_profile_pic == '1') {

            if ($user->profile_pic) {
                Storage::disk('public')->delete($user->profile_pic);
            }
            
            $profilePicPath = null;
            
        } elseif ($request->hasFile('profile_pic')) {
            
            if ($user->profile_pic) {
                Storage::disk('public')->delete($user->profile_pic);
            }

            $profilePicPath = $request->file('profile_pic')->store('profile_pics', 'public');
        }

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'employee_id' => $validated['employee_id'],
            'ic_number' => $validated['ic_number'],
            'phone_number' => $validated['phone_number'],
            'email' => $validated['email'],
            'profile_pic' => $profilePicPath
            
        ]);

        if ($request->remove_profile_pic == '1') {
            
            return redirect('/editprofile');
        }

        return redirect('/userprofile')->with('success', 'Profile updated successfully!');
    }
        
}
