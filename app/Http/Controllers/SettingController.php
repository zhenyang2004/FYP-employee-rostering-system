<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveType;
use App\Models\RosterSetting;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{

    private function isManager() {
        return optional(auth()->user()->employee)->role == 'Manager';
    }
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

        $hasPermission = $this->isManager();
        if (!$hasPermission) {
            $users = collect();
            return view('setting', compact('breadcrumbs', 'users', 'hasPermission'));
        }

        $leaveTypes = LeaveType::orderBy('name', 'asc')->get();
        $rosterSetting = RosterSetting::getSettings();

        return view('setting', compact('breadcrumbs', 'leaveTypes', 'rosterSetting', 'hasPermission'));
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

        return redirect()->route('setting')->with('success', 'Leave type added successfully!')->with('active_tab', 'leave-types');
    }

    public function destroyLeaveType($id) {

        $leaveType = LeaveType::findOrFail($id);
        $leaveType->delete();

        return redirect()->route('setting')->with('success', 'Leave type deleted successfully!')->with('active_tab', 'leave-types');
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

        return redirect()->route('setting')->with('success', 'Leave type updated successfully!')->with('active_tab', 'leave-types');
    }

    public function updateRosterSetting(Request $request) {

        $validated = $request->validate([
            'max_weekly_hours' => 'required|integer|min:1',
            'shift_duration_hours' => 'required|integer|min:1',

            'morning_start_time' => 'required|date_format:H:i',
            'morning_end_time' => 'required|date_format:H:i',

            'afternoon_start_time' => 'required|date_format:H:i',
            'afternoon_end_time' => 'required|date_format:H:i',

            'night_start_time' => 'required|date_format:H:i',
            'night_end_time' => 'required|date_format:H:i',
        ]);

        $rosterSetting = RosterSetting::getSettings();

        $rosterSetting->update([
            'max_weekly_hours' => $validated['max_weekly_hours'],
            'shift_duration_hours' => $validated['shift_duration_hours'],
            'morning_start_time' => $validated['morning_start_time'] . ':00',
            'morning_end_time' => $validated['morning_end_time'] . ':00',
            'afternoon_start_time' => $validated['afternoon_start_time'] . ':00',
            'afternoon_end_time' => $validated['afternoon_end_time'] . ':00',
            'night_start_time' => $validated['night_start_time'] . ':00',
            'night_end_time' => $validated['night_end_time'] . ':00',
        ]);

        return redirect()->route('setting')->with('success', 'Roster settings updated successfully!')->with('active_tab', 'general');
    }
}
