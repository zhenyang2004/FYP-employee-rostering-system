<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmployeeDetailController extends Controller
{
    public function index() {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Employee Details',
            'url' => route('employeedetails')
        ];

        return view('employeedetails', compact('breadcrumbs'));
    }
}
