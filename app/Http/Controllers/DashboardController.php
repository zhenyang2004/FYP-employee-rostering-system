<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
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

        if($role == 'Manager') {
            return view('managerdashboard', compact('breadcrumbs', 'leaveSummaries'));
        }

        return view('dashboard', compact('breadcrumbs', 'leaveSummaries'));    
    
    }

    public function show(Request $request){
        //
    }
}
