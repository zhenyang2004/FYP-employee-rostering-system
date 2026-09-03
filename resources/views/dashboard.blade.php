@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>Dashboard</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb dashboard-breadcrumb">
                        @foreach ($breadcrumbs as $breadcrumb)
                            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}" @if ($loop->last) aria-current="page" @endif>
                                
                                @if (!$loop->last)
                                    <a href="{{ $breadcrumb['url'] }}" > {{ $breadcrumb['text'] }}</a>
                                @else
                                    {{ $breadcrumb['text'] }}
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
            </div>

            {{-- Welcome Section --}}
            @php
                $loginUser = auth()->user();
                $userName = trim(($loginUser->first_name ?? '') . ' ' . ($loginUser->last_name ?? ''));
                $userRole = optional($loginUser->employee)->role ?? 'Employee';
            @endphp

            <div class="dashboard-welcome-card mb-3">
                <div>
                    <div class="dashboard-welcome-title">
                        Welcome back, {{ $userName }}
                    </div>

                    <div class="dashboard-welcome-subtitle">
                        Here is your {{ strtolower($userRole) }} dashboard overview.
                    </div>
                </div>

                <div class="dashboard-welcome-role">
                    {{ $userRole }}
                </div>
            </div>

            {{-- Top Summary Row --}}
            <div class="dashboard-top-summary-row">
                {{-- Next Shift Summary --}}
                <div class="employee-list-panel next-shift-panel mb-3">
                    <div class="employee-list-panel-header">
                        <div class="employee-list-panel-title">
                            <div>
                                <i class="fa fa-clock-o"></i>
                                <span>Next Shift</span>
                            </div>
                        </div>
                    </div>

                    <div class="employee-list-panel-body">
                        <div class="next-shift-card">
                            @if ($nextShift)
                                <div class="next-shift-date">
                                    {{ \Carbon\Carbon::parse($nextShift->roster_date)->format('d/m/Y') }}
                                </div>

                                <div class="next-shift-main">
                                    {{ $nextShift->shift_type }}
                                </div>

                                <div class="next-shift-time">
                                    {{ \Carbon\Carbon::parse($nextShift->shift_start_time)->format('h:i A') }}
                                    -
                                    {{ \Carbon\Carbon::parse($nextShift->shift_end_time)->format('h:i A') }}
                                </div>
                            @else
                                <div class="next-shift-main">
                                    No upcoming shift found
                                </div>

                                <div class="next-shift-time">
                                    You are not assigned to any upcoming roster yet.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                 {{-- Week Work Summary --}}
                <div class="employee-list-panel week-work-summary-panel mb-3">
                    <div class="employee-list-panel-header">
                        <div class="employee-list-panel-title">
                            <div>
                                <i class="fa fa-bar-chart"></i>
                                <span>This Week Work Summary</span>
                            </div>
                        </div>
                    </div>

                    <div class="employee-list-panel-body">
                        <div class="week-work-summary-card">
                            <div class="week-work-summary-grid">
                                <div class="week-work-summary-item">
                                    <div class="week-work-summary-value">{{ $workingDays }}</div>
                                    <div class="week-work-summary-label">Working Days</div>
                                </div>
                                <div class="week-work-summary-item">
                                    <div class="week-work-summary-value">{{ $offDays }}</div>
                                    <div class="week-work-summary-label">Off Days</div>
                                </div>

                                <div class="week-work-summary-item">
                                    <div class="week-work-summary-value">{{ $leaveDays }}</div>
                                    <div class="week-work-summary-label">Leave Days</div>
                                </div>
                            </div>
                            <div class="week-work-hours">
                                <div class="week-work-hours-label">Total Working Hours</div>

                                <div class="week-work-hours-value">
                                    {{ number_format($weeklyTotalHours, 1) }}
                                    /
                                    {{ $maxWeeklyHours }}
                                    hours
                                </div>

                                <progress class="week-work-progress-native" value="{{ $weeklyTotalHours }}"  max="{{ $maxWeeklyHours }}"></progress>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="employee-list-panel dashboard-quick-action-panel mb-3">
                    <div class="employee-list-panel-header">
                        <div class="employee-list-panel-title">
                            <div>
                                <i class="fa fa-bolt"></i>
                                <span>Quick Actions</span>
                            </div>
                        </div>
                    </div>

                    <div class="employee-list-panel-body">
                        <div class="dashboard-quick-action-card">

                            <a href="{{ route('leaverequest') }}" class="dashboard-quick-action-link">
                                <i class="fa fa-calendar-times-o"></i>
                                <span>Apply Leave</span>
                            </a>
                            <a href="{{ route('preferencesrequest') }}" class="dashboard-quick-action-link">
                                <i class="fa fa-sliders"></i>
                                <span>Submit Preference</span>
                            </a>
                            <a href="{{ route('shiftswaprequest') }}" class="dashboard-quick-action-link">
                                <i class="fa fa-exchange"></i>
                                <span>Request Shift Swap</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- My Weekly Roster --}}
            <div class="employee-list-panel mb-3">
                <div class="employee-list-panel-header">
                    <div class="employee-list-panel-title">
                        <div>
                            <i class="fa fa-calendar"></i>
                            <span>My Weekly Roster</span>
                        </div>
                    </div>

                    <div>
                        <a href="{{ route('viewroster') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fa fa-eye"></i>
                            Full Roster
                        </a>
                    </div>
                </div>

                <div class="employee-list-panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered employee-table">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Date</th>
                                    <th>Shift</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($weeklyRoster as $roster)
                                    <tr>
                                        <td>{{ $roster['day'] }}</td>
                                        <td>{{ $roster['date'] }}</td>
                                        <td>{{ $roster['shift'] }}</td>
                                        <td>{{ $roster['time'] }}</td>

                                        <td>
                                            @if ($roster['status'] == 'Working')
                                                <span class="badge bg-primary">Working</span>
                                            @elseif ($roster['status'] == 'Leave')
                                                <span class="badge bg-success">Leave</span>
                                            @elseif ($roster['status'] == 'Off Day')
                                                <span class="badge bg-secondary">Off Day</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $roster['status'] }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            No weekly roster found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- My Request Summary --}}
            <div class="employee-list-panel mb-3">
                <div class="employee-list-panel-header">
                    <div class="employee-list-panel-title">
                        <div>
                            <i class="fa fa-list-alt"></i>
                            <span>My Request Summary</span>
                        </div>
                    </div>
                </div>

                <div class="employee-list-panel-body">
                    <div class="request-summary-container">

                        <div class="request-summary-card">
                            <div class="request-summary-icon">
                                <i class="fa fa-calendar-times-o"></i>
                            </div>

                            <div class="request-summary-info">
                                <div class="request-summary-title">Pending Leave Requests</div>
                                <div class="request-summary-value">{{ $pendingLeaveCount }}</div>
                            </div>
                        </div>

                        <div class="request-summary-card">
                            <div class="request-summary-icon">
                                <i class="fa fa-exchange"></i>
                            </div>

                            <div class="request-summary-info">
                                <div class="request-summary-title">My Pending Shift Swaps</div>
                                <div class="request-summary-value">{{ $myPendingShiftSwapCount }}</div>
                            </div>
                        </div>

                        <div class="request-summary-card {{ $swapRequestsToMeCount > 0 ? 'request-summary-alert' : '' }}">
                            <div class="request-summary-icon">
                                <i class="fa fa-paper-plane"></i>
                            </div>

                            <div class="request-summary-info">
                                <div class="request-summary-title">Swap Requests To Me</div>
                                <div class="request-summary-value">{{ $swapRequestsToMeCount }}</div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Leave Balance Summary --}}
            <div class="employee-list-panel mb-3">
                <div class="employee-list-panel-header">
                    <div class="employee-list-panel-title">
                        <div>
                            <i class="fa fa-calendar-check-o"></i>
                            <span>Leave Balance Summary</span>
                        </div>
                    </div>
                </div>

                <div class="employee-list-panel-body">
                    <div class="leave-summary-container">

                        @forelse ($leaveSummaries as $leaveSummary)

                            @php
                                $statusClass = 'leave-safe';
                                $statusText = 'Available';

                                if ($leaveSummary['is_unlimited']) {

                                    $statusClass = 'leave-unlimited';
                                    $statusText = 'Unlimited';

                                } else {

                                    $entitlement = $leaveSummary['entitlement_days'];
                                    $remaining = $leaveSummary['remaining_days'];

                                    $percentage = $entitlement > 0 ? ($remaining / $entitlement) * 100 : 0;

                                    if ($percentage <= 25) {

                                        $statusClass = 'leave-danger';
                                        $statusText = 'Low Balance';

                                    } else if ($percentage <= 50) {

                                        $statusClass = 'leave-warning';
                                        $statusText = 'Running Low';
                                        
                                    } else {

                                        $statusClass = 'leave-safe';
                                        $statusText = 'Available';
                                    }
                                }
                            @endphp

                            <div class="leave-summary-card {{ $statusClass }}">

                                <div class="leave-summary-header">
                                    
                                    <div class="leave-summary-title">
                                        {{ $leaveSummary['name'] }}
                                    </div>

                                    <span class="leave-status">{{ $statusText }}</span>
                                </div>

                                <div class="leave-summary-main">
                                    @if ($leaveSummary['is_unlimited'])
                                        N/A
                                        <span>no limit</span>
                                    @else
                                        {{ $leaveSummary['remaining_days'] }}
                                        <span>days left</span>
                                    @endif
                                </div>

                                <div class="leave-summary-details">
                                    <div>
                                        <span>Entitlement</span>
                                        <strong>
                                            @if ($leaveSummary['is_unlimited'])
                                                N/A
                                            @else
                                                {{ $leaveSummary['entitlement_days'] }}
                                            @endif
                                        </strong>
                                    </div>
                                    <div>
                                        <span>Used</span>
                                        <strong>{{ $leaveSummary['used_days'] }}</strong>
                                    </div>
                                    <div>
                                        <span>Pending</span>
                                        <strong>{{ $leaveSummary['pending_days'] }}</strong>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted">
                                No leave type records found.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection