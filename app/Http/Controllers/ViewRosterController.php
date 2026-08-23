<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roster;
use App\Models\RosterDetail;
use App\Models\RosterShiftRequirement;
use App\Models\RosterAdjustmentLog;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\EmployeePreference;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ViewRosterController extends Controller
{
    public function index(Request $request) {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'View Roster',
            'url' => route('viewroster')
        ];

        $rosters = Roster::orderBy('start_date', 'desc')->get();

        $selectedRoster = null;
        $dates = collect();
        $groupedRoster = [];
        $groupedRequirements = [];
        $understaffedShifts = collect();
        $adjustmentLogs = collect();

        if ($rosters->count() > 0) {

            $selectedRosterId = $request->roster_id ?? $rosters->first()->id;
            $selectedRoster = Roster::find($selectedRosterId);


            /* Generate date range */
            if ($selectedRoster) {

                $period = CarbonPeriod::create($selectedRoster->start_date, $selectedRoster->end_date);
            
                foreach ($period as $date) {

                    $dates->push($date->copy());
                }

                $rosterDetails = RosterDetail::with('user')->where('roster_id', $selectedRoster->id)->orderBy('roster_date', 'asc')->orderBy('shift_type', 'asc')->get();

                foreach ($rosterDetails as $detail) {
                    $dataKey = Carbon::parse($detail->roster_date)->format('Y-m-d');
                    $groupedRoster[$dataKey][$detail->shift_type][] = $detail;
                }

                $shiftRequirements = RosterShiftRequirement::where('roster_id', $selectedRoster->id)->orderBy('roster_date', 'asc')->orderBy('shift_type', 'asc')->get();

                foreach ($shiftRequirements as $requirement) {
                    $dataKey = Carbon::parse($requirement->roster_date)->format('Y-m-d');
                    $groupedRequirements[$dataKey][$requirement->shift_type] = $requirement;
                }

                $understaffedShifts = $shiftRequirements->filter(function ($requirement) {
                    return $requirement->assigned_staff < $requirement->required_staff;
                })->values();

                $adjustmentLogs = RosterAdjustmentLog::with('user')->where('roster_id', $selectedRoster->id)->where('status', 'Unresolved')->orderBy('roster_date', 'asc')->orderBy('shift_type', 'asc')->get();
            }
        }

        $shiftTypes = [
            'Morning Shift' => [
                'label' => 'Morning',
                'time' => '08:00 - 16:00'
            ],
            'Afternoon Shift' => [
                'label' => 'Afternoon',
                'time' => '14:00 - 22:00'
            ],
            'Night Shift' => [
                'label' => 'Night',
                'time' => '22:00 - 06:00'
            ],
        ];

        return view('viewroster', compact('breadcrumbs', 'rosters', 'selectedRoster', 'dates', 'groupedRoster', 'shiftTypes', 'groupedRequirements', 'understaffedShifts', 'adjustmentLogs'));
    }

    public function editRoster(Request $request, $id) {

        if (optional(auth()->user()->employee)->role != 'Manager') {
            return redirect()->route('viewroster')->withErrors(['permission' => 'You do not have permission to edit roster.']);
        }
        
        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'View Roster',
            'url' => route('viewroster')
        ];

        $breadcrumbs[] = [
            'text' => 'Edit Roster',
            'url' => route('editroster', $id)
        ];

        $roster = Roster::findorFail($id);

        $dates = collect();
        $groupedRoster = [];
        $groupedRequirements = [];
        $availableStaff = [];
        $understaffedShifts = collect();

        $period = CarbonPeriod::create($roster->start_date, $roster->end_date);

        foreach ($period as $date) {
            $dates->push($date->copy());
        }

        $rosterDetails = RosterDetail::with('user')->where('roster_id', $roster->id)->orderBy('roster_date', 'asc')->orderBy('shift_type', 'asc')->get();

        foreach ($rosterDetails as $detail) {
            $dateKey = Carbon::parse($detail->roster_date)->format('Y-m-d');
            $groupedRoster[$dateKey][$detail->shift_type][] = $detail;
        }

        $shiftRequirements = RosterShiftRequirement::where('roster_id', $roster->id)->orderBy('roster_date', 'asc')->orderBy('shift_type', 'asc')->get();

        foreach ($shiftRequirements as $requirement) {
            $dateKey = Carbon::parse($requirement->roster_date)->format('Y-m-d');
            $groupedRequirements[$dateKey][$requirement->shift_type] = $requirement;

            if ($requirement->assigned_staff < $requirement->required_staff) {
                $understaffedShifts->push($requirement);
            }

            $availableStaff[$dateKey][$requirement->shift_type] = $this->getAvailableStaffForShift($roster->id, $requirement);
        }

        $shiftTypes = [
            'Morning Shift' => [
                'label' => 'Morning',
                'time' => '08:00 - 16:00'
            ],
            'Afternoon Shift' => [
                'label' => 'Afternoon',
                'time' => '14:00 - 22:00'
            ],
            'Night Shift' => [
                'label' => 'Night',
                'time' => '22:00 - 06:00'
            ],
        ];

        return view('editroster', compact('breadcrumbs', 'roster', 'dates', 'groupedRoster', 'groupedRequirements', 'availableStaff', 'understaffedShifts', 'shiftTypes'));
    }

    // Get staff who is active, not on leave, not assigned to this shift and not marked as unavailable
    private function getAvailableStaffForShift($rosterId, $requirement) {

        $rosterDate = Carbon::parse($requirement->roster_date)->format('Y-m-d');

        $leaveUserIds = LeaveRequest::where('status', 'Approved')->whereDate('start_date', '<=', $rosterDate)->whereDate('end_date', '>=', $rosterDate)->pluck('user_id')->toArray();
        $assignedUserIds = RosterDetail::where('roster_id', $rosterId)->whereDate('roster_date', $rosterDate)->pluck('user_id')->toArray();
        $unavailableUserIds = EmployeePreference::whereDate('preference_date', $rosterDate)->where('preference_type', 'Unavailable')->pluck('user_id')->toArray();

        $employees = User::with('employee')->where('status', 'Active')->whereHas('employee', function ($q) {
            $q->whereIn('role', ['Staff', 'Manager']);
        })->whereNotIn('id', $leaveUserIds)->whereNotIn('id', $assignedUserIds)->whereNotIn('id', $unavailableUserIds)->get();

        return $employees;

    }

    public function addRosterStaff(Request $request, $id) {

        if (optional(auth()->user()->employee)->role != 'Manager') {
            return redirect()->route('viewroster')->withErrors(['permission' => 'You do not have permission to add roster staff.']);
        }

        $validated = $request->validate([
            'roster_shift_requirement_id' => 'required|exists:roster_shift_requirements,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $roster = Roster::findorFail($id);

        $requirement = RosterShiftRequirement::where('roster_id', $roster->id)->where('id', $validated['roster_shift_requirement_id'])->firstOrFail();

        if ($requirement->assigned_staff >= $requirement->required_staff) {
            return redirect()->route('viewroster')->withErrors(['roster' => 'This shift is already filled.']);
        }

        $user = User::with('employee')->where('id', $validated['user_id'])->where('status', 'Active')->whereHas('employee', function ($q) {
            $q->whereIn('role', ['Staff', 'Manager']);
        })->firstOrFail();

        $rosterDate = Carbon::parse($requirement->roster_date)->format('Y-m-d');

        $hasLeave = LeaveRequest::where('user_id', $user->id)->where('status', 'Approved')->whereDate('start_date', '<=', $rosterDate)->whereDate('end_date', '>=', $rosterDate)->exists();

        if ($hasLeave) {
            return redirect()->route('editroster', $roster->id)->withErrors(['roster' => 'This user has a leave request for this date.']);
        }

        $alreadyAssigned = RosterDetail::where('roster_id', $roster->id)->where('user_id', $user->id)->whereDate('roster_date', $rosterDate)->exists();

        if ($alreadyAssigned) {
            return redirect()->route('editroster', $roster->id)->withErrors(['roster' => 'This user is already assigned to another shift.']);
        }

        $preference = EmployeePreference::where('user_id', $user->id)->whereDate('preference_date', $rosterDate)->first();

        $preferenceType = 'No Preference';
        $preferenceResult = 'No Preference';

        if ($preference) {
            if ($preference->preference_type == 'Unavailable') {
                return redirect()->route('editroster', $roster->id)->withErrors(['roster' => 'This user is marked as unavailable.']);
            }

            if ($preference->preference_type == 'Preferred Shift') {
                $preferenceType = 'Preferred Shift';

                if ($preference->shift_type == $requirement->shift_type) {
                    $preferenceResult = 'Matched';
                } else {
                    $preferenceResult = 'Not Matched';
                }
            }

            if ($preference->preference_type == 'Any Shift') {
                if ($preference->available_from) {
                    if (strtotime($requirement->shift_start_time) < strtotime($preference->available_from)) {
                        return redirect()
                            ->route('editroster', $roster->id)
                            ->withErrors(['roster' => 'This employee is not available from the start time of this shift.']);
                    }
                }

                $preferenceType = 'Any Shift';
                $preferenceResult = 'Assigned';
            }  
        }

        RosterDetail::create([
            'roster_id' => $roster->id,
            'roster_shift_requirement_id' => $requirement->id,
            'user_id' => $user->id,
            'roster_date' => $requirement->roster_date,
            'shift_type' => $requirement->shift_type,
            'shift_start_time' => $requirement->shift_start_time,
            'shift_end_time' => $requirement->shift_end_time,
            'preference_type' => $preferenceType,
            'preference_result' => $preferenceResult,
        ]);

        $assignedStaff = RosterDetail::where('roster_shift_requirement_id', $requirement->id)->count();

        $requirement->update([
            'assigned_staff' => $assignedStaff,
            'status' => $assignedStaff >= $requirement->required_staff ? 'Filled' : 'Understaffed',
        ]);

        if ($assignedStaff >= $requirement->required_staff) {
            RosterAdjustmentLog::where('roster_id', $roster->id)->where('roster_shift_requirement_id', $requirement->id)->where('status', 'Unresolved')->update(['status' => 'Resolved']);
        }

        return redirect()->route('editroster', $roster->id)->with('success', 'Shift assigned to roster successfully.');
    }

    public function removeRosterStaff(Request $request, $id) {

        if (optional(auth()->user()->employee)->role != 'Manager') {
            return redirect()->route('viewroster')->withErrors(['permission' => 'You do not have permission to edit roster.']);
        }

        $validated = $request->validate([
            'roster_detail_id' => 'required|exists:roster_details,id',
        ]);

        $roster = Roster::findorFail($id);
        $rosterDetail = RosterDetail::where('roster_id', $roster->id)->where('id', $validated['roster_detail_id'])->firstOrFail();

        $shiftRequirement = RosterShiftRequirement::find($rosterDetail->roster_shift_requirement_id);

        $rosterDetail->delete();

        if ($shiftRequirement) {
            $assignedStaff = RosterDetail::where('roster_shift_requirement_id', $shiftRequirement->id)->count();

            $shiftRequirement->update([
                'assigned_staff' => $assignedStaff,
                'status' => $assignedStaff >= $shiftRequirement->required_staff ? 'Filled' : 'Understaffed',
            ]);
        }

        return redirect()->route('editroster', $roster->id)->with('success', 'Shift removed from roster successfully.');
    }

    
}



