<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GenerateRosterController extends Controller
{
    public function index() {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' =>route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Generate Roster',
            'url' => route('generateroster')
        ];

        return view('generateroster', compact('breadcrumbs'));
    }
}
