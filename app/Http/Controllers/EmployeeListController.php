<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\employee;

class EmployeeListController extends Controller
{
    public function index(Request $request) {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Employee List',
            'url' => route('employeelist')
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

        $users = $query->orderBy('id', 'asc')->get();

        return view('employeelist', compact('breadcrumbs', 'users'));

    }

    public function editEmployee($id) {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Employee List',
            'url' => route('employeelist')
        ];

        $breadcrumbs[] = [
            'text' => 'Edit Employee List',
            'url' => route('editemployee', $id)
        ];

        $user = User::with('employee')->findOrFail($id);

        return view('/editemployee', compact('breadcrumbs', 'user'));
    }

    public function updateEmployeeRole(Request $request, $id) {

        $validated = $request->validate([
            'role' => 'required|string|in:Staff,Manager',
        ]);

        $user = User::findOrFail($id);

        employee::updateOrCreate(
            ['user_id' => $user->id],
            ['role' => $validated['role']] 
        );

        return redirect()->route('employeelist')->with('success', 'Employee role updated successfully!');
    }
}
