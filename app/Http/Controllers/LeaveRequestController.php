<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function index() {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Leave Request',
            'url' => route('leaverequest')
        ];

        $leaveTypes = LeaveType::where('status', 'Enabled')->orderBy('name', 'asc')->get();

        $leaveRequests = LeaveRequest::with('leaveType')->where('user_id', auth()->id())->orderBy('created_at', 'asc')->get();

        return view('leaverequest', compact('breadcrumbs', 'leaveTypes', 'leaveRequests'));
    }

    public function storeLeaveRequest(Request $request) {

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg|max:2048',
        ]);

        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $totalDays = $startDate->diffInDays($endDate) + 1;
        $currentYear = $startDate->year;

        if ($leaveType->entitlement_days > 0) {

            $usedDays = LeaveRequest::where('user_id', auth()->id())->where('leave_type_id', $leaveType->id)->where('status', 'Approved')->whereYear('start_date', $currentYear)->sum('total_days');
            $pendingDays = LeaveRequest::where('user_id', auth()->id())->where('leave_type_id', $leaveType->id)->where('status', 'Pending')->whereYear('start_date', $currentYear)->sum('total_days');
            $availableDays = $leaveType->entitlement_days - $usedDays - $pendingDays;

            if ($totalDays > $availableDays) {
                return back()->withInput()->withErrors(['leave_balance' => 'You have ' . $availableDays . ' days available for ' . $leaveType->name . '.']);
            }
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave_attachments', 'public');
        }

        LeaveRequest::create([
            'user_id' => auth()->id(),
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'attachment' => $attachmentPath,
            'status' => 'Pending',
        ]);

        return redirect()->route('leaverequest')->with('success', 'Leave request submitted successfully!');


    }


}
