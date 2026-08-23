<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\RosterDetail;
use App\Models\RosterShiftRequirement;
use App\Models\RosterAdjustmentLog;
use Illuminate\Support\Facades\DB;

class ManageRequestController extends Controller
{
    public function index() {
        
        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Manage Request',
            'url' => route('managerequest')
        ];

        $hasPermission = optional(auth()->user()->employee)->role == 'Manager';

        if (!$hasPermission) {
            $leaveRequests = collect();
            return view('managerequest', compact('breadcrumbs', 'leaveRequests', 'hasPermission'));
        }

        $leaveRequests = LeaveRequest::with(['user', 'leaveType'])->orderBy('created_at', 'asc')->get();

        return view('managerequest', compact('breadcrumbs', 'leaveRequests', 'hasPermission'));
    }

    private function isManager() {

        return optional(auth()->user()->employee)->role == 'Manager';
    }

    public function updateLeaveRequest(Request $request, $id) {

        if (!$this->isManager()) {
            return redirect()->route('managerequest')->withErrors(['permission' => 'You do not have permission to manage leave requests.']);
        }

        $validated = $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'manager_remark' => 'nullable|string|max:500',
        ]);

        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($leaveRequest->user_id == auth()->id()) {
            return redirect()->route('managerequest')->withErrors(['leave_request' => 'You cannot update your own leave request.']);
        }

        if ($leaveRequest->status != 'Pending') {
            return redirect()->route('managerequest')->withErrors(['leave_request' => 'This leave request has already been reviewed.']);
        }

        DB::beginTransaction();

        try {
            $leaveRequest->update([
                'status' => $validated['status'],
                'manager_remark' => $validated['manager_remark'],
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            if ($validated['status'] == 'Approved') {

                $affectedRosterDetails = RosterDetail::where('user_id', $leaveRequest->user_id)->whereDate('roster_date' , '>=', $leaveRequest->start_date)->whereDate('roster_date' , '<=', $leaveRequest->end_date)->get();
                $affectedShiftRequirementIds = $affectedRosterDetails->pluck('roster_shift_requirement_id')->unique()->toArray();
                $removeRosterCount = $affectedRosterDetails->count();

                foreach ($affectedRosterDetails as $detail) {
                    RosterAdjustmentLog::create([
                        'roster_id' => $detail->roster_id,
                        'roster_shift_requirement_id' => $detail->roster_shift_requirement_id,
                        'leave_request_id' => $leaveRequest->id,
                        'user_id' => $leaveRequest->user_id,
                        'roster_date' => $detail->roster_date,
                        'shift_type' => $detail->shift_type,
                        'reason' => 'Removed due to approved leave',
                        'status' => 'Unresolved',
                    ]);
                }

                if ($removeRosterCount > 0) {
                    RosterDetail::whereIn('id', $affectedRosterDetails->pluck('id'))->delete();
                }

                foreach ($affectedShiftRequirementIds as $shiftRequirementId) {
                    
                    $shiftRequirement = RosterShiftRequirement::find($shiftRequirementId);
                    
                    if  ($shiftRequirement) {
                        $assignedStaff = RosterDetail::where('roster_shift_requirement_id', $shiftRequirement->id)->count();

                        $shiftRequirement->update([
                            'assigned_staff' => $assignedStaff,
                            'status' => $assignedStaff >= $shiftRequirement->required_staff ? 'Filled' : 'Understaffed',
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('managerequest')->with('success', 'Leave request updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('managerequest')->withErrors(['leave_request' => 'Something went wrong. Please try again.']);
        }
    }
}
