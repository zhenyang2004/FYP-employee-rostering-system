<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveType;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function index() {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];
        
        $breadcrumbs[] = [
            'text' => 'Settings',
            'url' => route('setting')
        ];

        $leaveTypes = LeaveType::orderBy('name', 'asc')->get();

        return view('setting', compact('breadcrumbs', 'leaveTypes'));
    }

    public function saveLeaveType(Request $request) {

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:leave_types,name',
            'entitlement_days' => 'required|numeric|min:0',
        ]);

        LeaveType::create([
            'name' => $validated['name'],
            'entitlement_days' => $validated['entitlement_days'],
            'status' => 'Enabled',
        ]);

        return redirect()->route('setting')->with('success', 'Leave type added successfully!');
    }

    public function destroyLeaveType($id) {

        $leaveType = LeaveType::findOrFail($id);
        $leaveType->delete();

        return redirect()->route('setting')->with('success', 'Leave type deleted successfully!');
    }

    public function editLeaveType($id) {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];
        
        $breadcrumbs[] = [
            'text' => 'Settings',
            'url' => route('setting')
        ];

        $breadcrumbs[] = [
            'text' => 'Edit Leave Type',
            'url' => route('editleavetype', $id)
        ];

        $leaveType = LeaveType::findOrFail($id);

        return view('editleavetype', compact('breadcrumbs', 'leaveType'));

    }

    public function updateLeaveType(Request $request, $id) {

        $leaveType = LeaveType::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('leave_types', 'name')->ignore($leaveType->id)],
            'entitlement_days' => 'required|numeric|min:0',
            'status' => 'required|string|in:Enabled,Disabled',
        ]);


        $leaveType->update([
            'name' => $validated['name'],
            'entitlement_days' => $validated['entitlement_days'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('setting')->with('success', 'Leave type updated successfully!');
    }
}
