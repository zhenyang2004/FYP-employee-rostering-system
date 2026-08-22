<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\employee;
use App\Models\LeaveRequest;
use App\Models\PreferenceRequest;

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

        if ($request->filled('ic_number')) {
            $query->where('ic_number', 'like', '%' . $request->ic_number . '%');
        }

        if ($request->filled('phone_number')) {
            $query->where('phone_number', 'like', '%' . $request->phone_number . '%');
        }

        $users = $query->orderBy('id', 'asc')->get();

        return view('employeedetails', compact('breadcrumbs', 'users'));
    }

    public function viewEmployeeDetail($id) {

        $user = User::with('employee')->findOrFail($id);
        $leaveRequests = LeaveRequest::with('leaveType')->where('user_id', $user->id)->orderBy('created_at', 'asc')->get();
        $preferenceRequests = PreferenceRequest::where('user_id', $user->id)->orderBy('start_date', 'asc')->get();

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Employee Details',
            'url' => route('employeedetails')
        ];

        $breadcrumbs[] = [
            'text' => 'View Employee Details',
            'url' => route('viewemployeedetails', $id)
        ];

        return view('viewemployeedetails', compact('breadcrumbs', 'user', 'leaveRequests', 'preferenceRequests'));

    }

    public function viewPreferencesHistory($id) {

        $preferenceRequest = PreferenceRequest::with(['preferences' => function ($query) {
            $query->orderBy('preference_date', 'asc');
        },'user'])->findOrFail($id);

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Employee Details',
            'url' => route('employeedetails')
        ];

        $breadcrumbs[] = [
            'text' => 'View Employee Details',
            'url' => route('viewemployeedetails', $preferenceRequest->user_id)
        ];

        $breadcrumbs[] = [
            'text' => 'View Preferences History',
            'url' => route('viewpreferencesrequesthistory', $preferenceRequest->id)
        ];

        return view('viewpreferencesrequesthistory', compact('breadcrumbs', 'preferenceRequest')); 
    }
}
