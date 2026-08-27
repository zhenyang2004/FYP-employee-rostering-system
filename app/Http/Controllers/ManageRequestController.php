<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\RosterDetail;
use App\Models\RosterShiftRequirement;
use App\Models\RosterAdjustmentLog;
use App\Models\ShiftSwapRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        $shiftSwapRequests = ShiftSwapRequest::with(['requester', 'targetUser', 'requesterRosterDetail', 'targetRosterDetail'])->whereIn('status', ['Pending Manager Approval', 'Approved', 'Rejected by Manager'])->orderBy('created_at', 'asc')->get();

        return view('managerequest', compact('breadcrumbs', 'leaveRequests', 'hasPermission', 'shiftSwapRequests'));
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

    public function updateShiftSwapRequest(Request $request, $id) {

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'manager_remark' => 'nullable|string|max:500',
        ]);

        $swapRequest = ShiftSwapRequest::with(['requesterRosterDetail', 'targetRosterDetail'])->where('id', $id)->where('status', 'Pending Manager Approval')->firstOrFail();

        if ($validated['action'] == 'reject') {
            $swapRequest->update([
                'status' => 'Rejected by Manager',
                'manager_remark' => $validated['manager_remark'],
                'reviewed_by' => auth()->id(),
                'reviewed_at' => Carbon::now(),
            ]);

            return redirect()->route('managerequest')->with('success', 'Shift swap request rejected successfully.');
        }

        $requesterDetail = $swapRequest->requesterRosterDetail;
        $targetDetail = $swapRequest->targetRosterDetail;

        if (!$requesterDetail || !$targetDetail) {
            return redirect()->route('managerequest')->withErrors(['swap' => 'Shift swap request is invalid.']);

        }

        if (Carbon::parse($requesterDetail->roster_date)->lte(Carbon::today()) || Carbon::parse($targetDetail->roster_date)->lte(Carbon::today())) {
            return redirect()->route('managerequest')->withErrors(['swap' => 'Shift swap request cannot be approved because the shift has already passed.']);

        }

        if ($requesterDetail->user_id != $swapRequest->requester_user_id || $targetDetail->user_id != $swapRequest->target_user_id) {
            return redirect()->route('managerequest')->withErrors(['swap' => 'This shift swap request cannot be approved because the roster has already changed.']);

        }

        $requesterConflict = RosterDetail::where('roster_id', $swapRequest->roster_id)->where('user_id', $swapRequest->requester_user_id)->whereDate('roster_date', $targetDetail->roster_date)->whereNotIn('id', [$requesterDetail->id, $targetDetail->id])->exists();
        if ($requesterConflict) {
            return redirect()->route('managerequest')->withErrors(['swap' => 'Shift swap request cannot be approved because the requester has another shift on the same day.']);
        }

        $targetConflict = RosterDetail::where('roster_id', $swapRequest->roster_id)->where('user_id', $swapRequest->target_user_id)->whereDate('roster_date', $requesterDetail->roster_date)->whereNotIn('id', [$requesterDetail->id, $targetDetail->id])->exists();
        if ($targetConflict) {
            return redirect()->route('managerequest')->withErrors(['swap' => 'Shift swap request cannot be approved because the target has another shift on the same day.']);
        }

        DB::transaction(function () use ($swapRequest, $requesterDetail, $targetDetail, $validated) {

            if ($requesterDetail->roster_date == $targetDetail->roster_date) {

                $requesterOriginal = [
                    'roster_shift_requirement_id' => $requesterDetail->roster_shift_requirement_id,
                    'roster_date' => $requesterDetail->roster_date,
                    'shift_type' => $requesterDetail->shift_type,
                    'shift_start_time' => $requesterDetail->shift_start_time,
                    'shift_end_time' => $requesterDetail->shift_end_time,
                ];

                $targetOriginal = [
                    'roster_shift_requirement_id' => $targetDetail->roster_shift_requirement_id,
                    'roster_date' => $targetDetail->roster_date,
                    'shift_type' => $targetDetail->shift_type,
                    'shift_start_time' => $targetDetail->shift_start_time,
                    'shift_end_time' => $targetDetail->shift_end_time,
                ];

                $requesterDetail->update([
                    'roster_shift_requirement_id' => $targetOriginal['roster_shift_requirement_id'],
                    'roster_date' => $targetOriginal['roster_date'],
                    'shift_type' => $targetOriginal['shift_type'],
                    'shift_start_time' => $targetOriginal['shift_start_time'],
                    'shift_end_time' => $targetOriginal['shift_end_time'],
                    'preference_type' => 'Shift Swap',
                    'preference_result' => 'Manual Swap',
                ]);

                $targetDetail->update([
                    'roster_shift_requirement_id' => $requesterOriginal['roster_shift_requirement_id'],
                    'roster_date' => $requesterOriginal['roster_date'],
                    'shift_type' => $requesterOriginal['shift_type'],
                    'shift_start_time' => $requesterOriginal['shift_start_time'],
                    'shift_end_time' => $requesterOriginal['shift_end_time'],
                    'preference_type' => 'Shift Swap',
                    'preference_result' => 'Manual Swap',
                ]);

            } else {

                $requesterDetail->update([
                    'user_id' => $swapRequest->target_user_id,
                    'preference_type' => 'Shift Swap',
                    'preference_result' => 'Manual Swap',
                ]);

                $targetDetail->update([
                    'user_id' => $swapRequest->requester_user_id,
                    'preference_type' => 'Shift Swap',
                    'preference_result' => 'Manual Swap',
                ]);
            }

            $swapRequest->update([
                'status' => 'Approved',
                'manager_remark' => $validated['manager_remark'],
                'reviewed_by' => auth()->id(),
                'reviewed_at' => Carbon::now(),
            ]);
        });

        return redirect()->route('managerequest')->with('success', 'Shift swap request approved successfully.');
    }

}
