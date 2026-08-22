<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\employee;

class EmployeeListController extends Controller
{
    public function index(Request $request) {

        if (!$this->isAdmin()) {
            
            $breadcrumbs = [];

            $breadcrumbs[] = [
                'text' => 'Home',
                'url' => route('dashboard')
            ];

            $breadcrumbs[] = [
                'text' => 'Employee List',
                'url' => route('employeelist')
            ];

            return view('permissiondenied', compact('breadcrumbs'));
        }

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Employee List',
            'url' => route('employeelist')
        ];

        $query = User::with('employee')->whereHas('employee', function ($q) {
            $q->whereIn('role', ['Staff', 'Manager']);
        });

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

    public function isAdmin() {

        return optional(auth()->user()->employee)->role == 'Admin';
    }

    public function toggleStatus($id) {
        
        if (!$this->isAdmin()) {

            return redirect()->route('employeelist')->withErrors(['permission' => 'You do not have permission to update employee status.']);
        }

        $user = User::with('employee')->findOrFail($id);

        if (auth()->id() == $user->id) {

            return redirect()->route('employeelist')->withErrors(['permission' => 'You cannot update your own status.']);
        }

        if (!$user->employee || $user->employee->role == 'Admin') {
            
            return redirect()->route('employeelist')->withErrors(['permission' => 'You cannot update admin status.']);
        }

        $user->update([
           'status' => $user->status == 'Active' ? 'Inactive' : 'Active' 
        ]);

        return redirect()->route('employeelist')->with('success', 'Employee status updated successfully!');
    }

}
