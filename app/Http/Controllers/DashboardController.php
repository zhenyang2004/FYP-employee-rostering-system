<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index() {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Dashboard',
            'url' => route('dashboard')
        ];

        $user = Auth::user();

        $role = optional($user->employee)->role;

        if($role == 'Manager') {
            return view('managerdashboard', compact('breadcrumbs'));
        }

        return view('dashboard', compact('breadcrumbs'));    
    
    }

    public function show(Request $request){
        //
    }
}
