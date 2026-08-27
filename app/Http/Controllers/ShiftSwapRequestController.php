<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShiftSwapRequest;
use App\Models\RosterDetail;
use App\Models\Roster;
use Carbon\Carbon;

class ShiftSwapRequestController extends Controller
{
    public function index(Request $request) {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' =>route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Shift Swap Request',
            'url' => route('shiftswaprequest')
        ];

        $userId = auth()->id();

        $availableRosterIds = RosterDetail::where('user_id', $userId)->whereDate('roster_date', '>', Carbon::today())->pluck('roster_id')->unique()->values();
        $rosters = Roster::whereIn('id', $availableRosterIds)->whereDate('end_date', '>=', Carbon::today())->orderBy('start_date', 'asc')->get();

        $selectedRosterId = $request->roster_id;
        if (!$selectedRosterId || !$rosters->contains('id', (int)$selectedRosterId)) {
            $selectedRosterId = null;
        }

        $myRosterDetails = collect();
        $targetRosterDetails = collect();

        if ($selectedRosterId) {

            $myRosterDetails = RosterDetail::with(['user', 'roster'])->where('roster_id', $selectedRosterId)->where('user_id', $userId)->whereDate('roster_date', '>', Carbon::today())->orderBy('roster_date', 'asc')->orderBy('shift_type', 'asc')->get();
            $targetRosterDetails = RosterDetail::with(['user', 'roster'])->where('roster_id', $selectedRosterId)->where('user_id', '!=', $userId)->whereDate('roster_date', '>', Carbon::today())->orderBy('roster_date', 'asc')->orderBy('shift_type', 'asc')->get();
        }

        $myShiftSwapRequests = ShiftSwapRequest::with(['targetUser', 'requesterRosterDetail', 'targetRosterDetail'])->where('requester_user_id', $userId)->orderBy('created_at', 'asc')->get();
        $requestsToMe = ShiftSwapRequest::with(['requester', 'requesterRosterDetail', 'targetRosterDetail'])->where('target_user_id', $userId)->orderBy('created_at', 'asc')->get();

        return view('shiftswaprequest', compact('breadcrumbs', 'rosters', 'selectedRosterId', 'myRosterDetails', 'targetRosterDetails', 'myShiftSwapRequests', 'requestsToMe'));
    }

    public function store(Request $request) {

        $validated = $request->validate([
            'roster_id' => 'required|exists:rosters,id',
            'requester_roster_detail_id' => 'required|exists:roster_details,id',
            'target_roster_detail_id' => 'required|exists:roster_details,id|different:requester_roster_detail_id',
            'reason' => 'nullable|string|max:500',
        ]);

        $requesterRosterDetail = RosterDetail::where('id', $validated['requester_roster_detail_id'])->where('roster_id', $validated['roster_id'])->where('user_id', auth()->id())->whereDate('roster_date', '>', Carbon::today())->firstOrFail();
        $targetRosterDetail = RosterDetail::where('id', $validated['target_roster_detail_id'])->where('roster_id', $validated['roster_id'])->where('user_id', '!=', auth()->id())->whereDate('roster_date', '>', Carbon::today())->firstOrFail();

        if ($requesterRosterDetail->roster_id != $targetRosterDetail->roster_id) {
            return redirect()->route('shiftswaprequest')->withErrors(['swap' => 'Shift swap can only be requested within the same roster period.'])->withInput();

        }

        $existingRequest = ShiftSwapRequest::where('requester_roster_detail_id', $requesterRosterDetail->id)->whereIn('status', ['Pending Staff Approval', 'Pending Manager Approval'])->exists();

        if ($existingRequest) {
            return redirect()->route('shiftswaprequest')->withErrors(['swap' => 'You already have a pending shift swap request for this shift.'])->withInput();

        }

        ShiftSwapRequest::create([
            'requester_user_id' => auth()->id(),
            'target_user_id' => $targetRosterDetail->user_id,
            'roster_id' => $requesterRosterDetail->roster_id,
            'requester_roster_detail_id' => $requesterRosterDetail->id,
            'target_roster_detail_id' => $targetRosterDetail->id,
            'reason' => $validated['reason'],
            'status' => 'Pending Staff Approval',
        ]);

        return redirect()->route('shiftswaprequest', ['roster_id' => $validated['roster_id']])->with('success', 'Shift swap request submitted successfully.');
        
    }

    public function accept($id) {

        $swapRequest = ShiftSwapRequest::where('id', $id)->where('target_user_id', auth()->id())->where('status', 'Pending Staff Approval')->firstOrFail();

        $swapRequest->update([
            'status' => 'Pending Manager Approval',
            'target_responded_at' => Carbon::now(),
        ]);

        return redirect()->route('shiftswaprequest')->with('success', 'Shift swap request accepted successfully. It is now pending manager approval.');
    }

    public function reject($id) {

        $swapRequest = ShiftSwapRequest::where('id', $id)->where('target_user_id', auth()->id())->where('status', 'Pending Staff Approval')->firstOrFail();

        $swapRequest->update([
            'status' => 'Rejected by Staff',
            'target_responded_at' => Carbon::now(),
        ]);

        return redirect()->route('shiftswaprequest')->with('success', 'Shift swap request rejected successfully.');
    }
}
