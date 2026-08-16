<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;

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

        $leaveRequest->update([
            'status' => $validated['status'],
            'manager_remark' => $validated['manager_remark'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('managerequest')->with('success', 'Leave request updated successfully.');
    }
}
