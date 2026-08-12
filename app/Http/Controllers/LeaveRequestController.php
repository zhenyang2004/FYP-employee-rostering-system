<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index() {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Leave Request',
            'url' => route('leaverequest')
        ];

        return view('leaverequest', compact('breadcrumbs'));
    }
}
