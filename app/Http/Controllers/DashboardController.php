<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\Roster;
use App\Models\RosterDetail;
use App\Models\RosterSetting;
use App\Models\ShiftSwapRequest;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index() {

        $breadcrumbs = [];

        $breadcrumbs[] = [
            'text' => 'Home',
            'url' => route('dashboard')
        ];

        $breadcrumbs[] = [
            'text' => 'Dashboard',
            'url' => route('dashboard')
        ];

        $user = Auth::user();
        $role = optional($user->employee)->role;

        $currentYear = Carbon::now()->year;
        $leaveTypes = LeaveType::where('status', 'Enabled')->orderBy('name', 'asc')->get();

        $leaveSummaries = $leaveTypes->map(function ($leaveType) use ($user, $currentYear) {

            $usedDays = LeaveRequest::where('user_id', $user->id)->where('leave_type_id', $leaveType->id)->where('status', 'Approved')->whereYear('start_date', $currentYear)->sum('total_days');

            $pendingDays = LeaveRequest::where('user_id', $user->id)->where('leave_type_id', $leaveType->id)->where('status', 'Pending')->whereYear('start_date', $currentYear)->sum('total_days');

            if ($leaveType->entitlement_days > 0) {
                $remainingDays = $leaveType->entitlement_days - $usedDays;

                if ($remainingDays < 0) {
                    $remainingDays = 0;
                }

                $isUnlimited = false;

            } else {
                $remainingDays = null;
                $isUnlimited = true;
            }

            return [
                'name' => $leaveType->name,
                'entitlement_days' => $leaveType->entitlement_days,
                'used_days' => $usedDays,
                'pending_days' => $pendingDays,
                'remaining_days' => $remainingDays,
                'is_unlimited' => $isUnlimited,
            ];
            
        });

        $userId = $user->id;
        $today = Carbon::today(); 
        $now = Carbon::now();

        $currentRoster = Roster::whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->orderBy('start_date', 'desc')->first();
        $dashboardRoster = $currentRoster;

        $nextShift = null;

        if ($currentRoster) {
            $nextShift = RosterDetail::with('roster')->where('user_id', $userId)->where('roster_id', $currentRoster->id)->whereDate('roster_date', '>=', $today)->get()->filter(function ($detail) use ($now) {
                $shiftStartDateTime = Carbon::parse($detail->roster_date . ' ' . $detail->shift_start_time);
                return $shiftStartDateTime->gt($now);
            })->sortBy(function ($detail) {
                return Carbon::parse($detail->roster_date . ' ' . $detail->shift_start_time)->timestamp;
            })->first();
        }

        if (!$nextShift) {
            $nextShift = RosterDetail::with('roster')->where('user_id', $userId)->whereHas('roster', function ($query) use ($today) {
                $query->whereDate('start_date', '>', $today);
            })->whereDate('roster_date', '>', $today)->get()->filter(function ($detail) use ($now) {
                $shiftStartDateTime = Carbon::parse($detail->roster_date . ' ' . $detail->shift_start_time);
                return $shiftStartDateTime->gt($now);
            })->sortBy(function ($detail) {
                return Carbon::parse($detail->roster_date . ' ' . $detail->shift_start_time)->timestamp;
            })->first();
        }

        $weeklyRoster = collect();

        $workingDays = 0;
        $offDays = 0;
        $leaveDays = 0;
        $weeklyTotalHours = 0;

        $rosterSetting = RosterSetting::getSettings();
        $maxWeeklyHours = $rosterSetting->max_weekly_hours;


        if ($dashboardRoster) {
            $weeklyStart = Carbon::parse($dashboardRoster->start_date);
            $weeklyEnd = Carbon::parse($dashboardRoster->end_date);

            $weekRosterDetails = RosterDetail::where('user_id', $userId)->where('roster_id', $dashboardRoster->id)->get()->keyBy(function ($detail) {
                return Carbon::parse($detail->roster_date)->format('Y-m-d');
            });
        

            $approvedLeaves = LeaveRequest::with('leaveType')->where('user_id', $userId)->where('status', 'Approved')->whereDate('start_date', '<=', $weeklyEnd->copy()->format('Y-m-d'))->whereDate('end_date', '>=', $weeklyStart->copy()->format('Y-m-d'))->get();
            $periodDays = $weeklyStart->diffInDays($weeklyEnd) + 1;

            for ($i = 0; $i < $periodDays; $i++) {

                $date = $weeklyStart->copy()->addDays($i);
                $dateKey = $date->format('Y-m-d');
                $shift = $weekRosterDetails->get($dateKey);

                $leave = $approvedLeaves->first(function ($leaveRequest) use ($date) {
                    return $date->between(Carbon::parse($leaveRequest->start_date), Carbon::parse($leaveRequest->end_date));
                });

                if ($leave) {
                    $status = 'Leave';
                    $shiftType = '-';
                    $shiftTime = '-';

                    $leaveDays++;

                } elseif ($shift) {
                    $status = 'Working';
                    $shiftType = $shift->shift_type;
                    $shiftTime = Carbon::parse($shift->shift_start_time)->format('h:i A') .
                        ' - ' .
                        Carbon::parse($shift->shift_end_time)->format('h:i A');

                    $workingDays++;

                    $shiftStart = Carbon::parse($shift->roster_date . ' ' . $shift->shift_start_time);
                    $shiftEnd = Carbon::parse($shift->roster_date . ' ' . $shift->shift_end_time);

                    if ($shiftEnd->lte($shiftStart)) {
                        $shiftEnd->addDay();
                    }

                    $weeklyTotalHours += $shiftStart->diffInMinutes($shiftEnd) / 60;

                } else {
                    $status = 'Off Day';
                    $shiftType = '-';
                    $shiftTime = '-';

                    $offDays++;
                }

                $weeklyRoster->push([
                    'day' => $date->format('l'),
                    'date' => $date->format('Y-m-d'),
                    'shift' => $shiftType,
                    'time' => $shiftTime,
                    'status' => $status,
                ]);
            }
        }

        $pendingLeaveCount = LeaveRequest::where('user_id', $userId)->where('status', 'Pending')->count();
        $myPendingShiftSwapCount = ShiftSwapRequest::where('requester_user_id', $userId)->whereIn('status', ['Pending Staff Approval', 'Pending Manager Approval'])->count();
        $swapRequestsToMeCount = ShiftSwapRequest::where('target_user_id', $userId)->where('status', 'Pending Staff Approval')->count();

        $pendingLeaveFromOthersCount = LeaveRequest::where('user_id', '!=', $userId)->where('status', 'Pending')->count();
        $pendingShiftSwapFromOthersCount = ShiftSwapRequest::where('requester_user_id', '!=', $userId)->where('status', 'Pending Manager Approval')->count();
        $pendingRequestsFromOthersCount = $pendingLeaveFromOthersCount + $pendingShiftSwapFromOthersCount;

        if($role == 'Manager') {
            return view('managerdashboard', compact('breadcrumbs', 'leaveSummaries', 'nextShift', 'dashboardRoster', 'weeklyRoster', 'pendingLeaveCount', 'myPendingShiftSwapCount', 'swapRequestsToMeCount', 'pendingRequestsFromOthersCount', 'pendingLeaveFromOthersCount', 'pendingShiftSwapFromOthersCount'));
        }

        return view('dashboard', compact('breadcrumbs', 'leaveSummaries', 'nextShift', 'dashboardRoster', 'weeklyRoster', 'pendingLeaveCount', 'myPendingShiftSwapCount', 'swapRequestsToMeCount', 'workingDays', 'offDays', 'leaveDays', 'weeklyTotalHours', 'maxWeeklyHours'));    
    
    }

    public function show(Request $request){
        //
    }
}
