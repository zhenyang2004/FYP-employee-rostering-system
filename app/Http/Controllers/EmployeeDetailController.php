<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\employee;

class EmployeeDetailController extends Controller
{
    public function index(Request $request) {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Employee Details',
            'url' => route('employeedetails')
        ];

        $query = User::with('employee');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', 'like', '%' . $request->employee_id . '%');
        }

        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->name . '%')->orWhere('last_name', 'like', '%' . $request->name . '%');
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('role', $request->role);
            });
        }

        if ($request->filled('ic_number')) {
            $query->where('ic_number', 'like', '%' . $request->ic_number . '%');
        }

        if ($request->filled('phone_number')) {
            $query->where('phone_number', 'like', '%' . $request->phone_number . '%');
        }

        $users = $query->orderBy('id', 'asc')->get();

        return view('employeedetails', compact('breadcrumbs', 'users'));
    }
}
