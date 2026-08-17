<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ViewRosterController extends Controller
{
    public function index() {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'View Roster',
            'url' => route('viewroster')
        ];

        return view('viewroster', compact('breadcrumbs'));
    }
}
