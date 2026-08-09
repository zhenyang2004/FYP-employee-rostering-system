<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

        return view('dashboard', compact('breadcrumbs'));    
    
    }

    public function show(Request $request){
        //
    }
}
