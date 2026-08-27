@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>View Employee Details</h2>
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

            <div class="profile-panel mb-3">
                <div class="profile-panel-header">
                    <div class="profile-panel-title">
                        <div>
                            <i class="fa fa-user-circle"></i>
                            <span>Employee Details</span>
                        </div>
                    </div>

                    <div class="profile-panel-actions">
                        <a href="{{ url('/employeedetails') }}" class="btn btn-danger" title="Back">
                            <i class="fa fa-reply"></i>
                        </a>
                    </div>
                </div>

                <div class="profile-panel-body">

                    @if (session('success'))
                        <div class="alert alert-success m-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="profile-form-row">
                        <label>Profile Picture</label>

                        @if ($user->profile_pic)
                            <img src="{{ asset('storage/' . $user->profile_pic) }}" class="profile-preview-img" alt="Profile Picture">
                        @else
                            <div class="profile-preview-default">
                                <i class="bi bi-person-circle"></i>
                            </div>
                        @endif
                    </div>

                    <div class="profile-form-row">
                        <label>First Name</label>
                        <input type="text" class="form-control" value="{{ $user->first_name }}" disabled>
                    </div>

                    <div class="profile-form-row">
                        <label>Last Name</label>
                        <input type="text" class="form-control" value="{{ $user->last_name }}" disabled>
                    </div>

                    <div class="profile-form-row">
                        <label>Employee ID</label>
                        <input type="text" class="form-control" value="{{ $user->employee_id }}" disabled>
                    </div>

                    <div class="profile-form-row">
                        <label>Role</label>
                        <input type="text" class="form-control" value="{{ $user->employee->role }}" disabled>
                    </div>

                    <div class="profile-form-row">
                        <label>Phone Number</label>
                        <input type="text" class="form-control" value="{{ $user->phone_number }}" disabled>
                    </div>
                    
                    <div class="profile-form-row">
                        <label>IC Number</label>
                        <input type="text" class="form-control" value="{{ $user->ic_number }}" disabled>
                    </div>

                    <div class="profile-form-row">
                        <label>Email</label>
                        <input type="text" class="form-control" value="{{ $user->email }}" disabled>
                    </div>
                </div>
            </div>

            {{-- Leave Request History --}}
           <div class="profile-panel mb-3">
                <div class="profile-panel-header leave-history-toggle" id="leaveHistoryToggle">
                    <div class="profile-panel-title">
                        <div>
                            <i class="fa fa-history"></i>
                            <span>Leave Request History</span>
                        </div>
                    </div>

                    <div class="leave-history-toggle-icon">
                        <i class="fa fa-chevron-down" id="leaveHistoryIcon"></i>
                    </div>
                </div>

                <div class="employee-list-panel-body" id="leaveHistoryBody">
                    <div class="table-responsive">
                        <table class="table table-bordered employee-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Total Days</th>
                                    <th>Reason</th>
                                    <th>Attachment</th>
                                    <th>Status</th>
                                    <th>Manager Remarks</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($leaveRequests as $index => $leaveRequest)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $leaveRequest->leaveType->name ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('d/m/Y') }}</td>
                                        <td>{{ $leaveRequest->total_days }}</td>
                                        <td>{{ $leaveRequest->reason ?? '-' }}</td>
                                        <td>
                                            @if ($leaveRequest->attachment)
                                                <a href="{{ asset('storage/' . $leaveRequest->attachment) }}" target="_blank" class="btn btn-info btn-sm">
                                                    <i class="fa fa-eye"></i>
                                                    View
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($leaveRequest->status == 'Pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif ($leaveRequest->status == 'Approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif ($leaveRequest->status == 'Rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $leaveRequest->status }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $leaveRequest->manager_remark ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No leave requests found !</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Preferences Request History --}}
            <div class="profile-panel mb-3">
                <div class="profile-panel-header preferences-history-toggle" id="preferencesHistoryToggle">
                    <div class="profile-panel-title">
                        <div>
                            <i class="fa fa-history"></i>
                            <span>Preferences Request History</span>
                        </div>
                    </div>

                    <div class="leave-history-toggle-icon">
                        <i class="fa fa-chevron-down" id="preferencesHistoryIcon"></i>
                    </div>
                </div>

                <div class="employee-list-panel-body" id="preferencesHistoryBody">
                    <div class="table-responsive">
                        <table class="table table-bordered employee-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead> 

                            <tbody>
                                @if ($preferenceRequests->count() > 0)
                                    @foreach ($preferenceRequests as $preferenceRequest)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ \Carbon\Carbon::parse($preferenceRequest->start_date)->format('d/m/Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($preferenceRequest->end_date)->format('d/m/Y') }}</td>
                                            <td>
                                                <a href="{{ url('/viewpreferencesrequesthistory', $preferenceRequest->id) }}" class="btn btn-primary btn-sm employee-action-btn" title="View">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No preferences request found !</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Shift Swap Request History --}}
            <div class="profile-panel mb-3">
                <div class="profile-panel-header preferences-history-toggle" id="shiftswapHistoryToggle">
                    <div class="profile-panel-title">
                        <div>
                            <i class="fa fa-history"></i>
                            <span>Shift Swap Request History</span>
                        </div>
                    </div>

                    <div class="leave-history-toggle-icon">
                        <i class="fa fa-chevron-down" id="shiftswapHistoryIcon"></i>
                    </div>
                </div>

                <div class="employee-list-panel-body" id="shiftswapHistoryBody">
                    <div class="table-responsive">
                        <table class="table table-bordered employee-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Swap With</th>
                                    <th>My Shift</th>
                                    <th>Target Shift</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Submitted At</th>
                                    <th>Manager Remarks</th>
                                    <th>Reviewed By</th>
                                    <th>Reviewed At</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($shiftSwapRequests as $index => $swapRequest)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $swapRequest->targetUser->first_name }} {{ $swapRequest->targetUser->last_name }}</td>
                                        <td>
                                            @if ($swapRequest->requesterRosterDetail)
                                                <strong>
                                                    {{ \Carbon\Carbon::parse($swapRequest->requesterRosterDetail->roster_date)->format('d/m/Y') }}
                                                </strong>
                                                <br>
                                                {{ $swapRequest->requesterRosterDetail->shift_type }}
                                                <br>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($swapRequest->requesterRosterDetail->shift_start_time)->format('H:i') }}
                                                    -
                                                    {{ \Carbon\Carbon::parse($swapRequest->requesterRosterDetail->shift_end_time)->format('H:i') }}
                                                </small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($swapRequest->targetRosterDetail)
                                                <strong>
                                                    {{ \Carbon\Carbon::parse($swapRequest->targetRosterDetail->roster_date)->format('d/m/Y') }}
                                                </strong>
                                                <br>
                                                {{ $swapRequest->targetRosterDetail->shift_type }}
                                                <br>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($swapRequest->targetRosterDetail->shift_start_time)->format('H:i') }}
                                                    -
                                                    {{ \Carbon\Carbon::parse($swapRequest->targetRosterDetail->shift_end_time)->format('H:i') }}
                                                </small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $swapRequest->reason ?? '-' }}</td>
                                        <td>
                                            @if ($swapRequest->status == 'Pending Staff Approval')
                                                <span class="badge bg-warning text-dark">Pending Staff Approval</span>
                                            @elseif ($swapRequest->status == 'Pending Manager Approval')
                                                <span class="badge bg-info text-dark">Pending Manager Approval</span>
                                            @elseif ($swapRequest->status == 'Approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif ($swapRequest->status == 'Rejected by Staff')
                                                <span class="badge bg-danger">Rejected by Staff</span>
                                            @elseif ($swapRequest->status == 'Rejected by Manager')
                                                <span class="badge bg-danger">Rejected by Manager</span>
                                            @elseif ($swapRequest->status == 'Cancelled')
                                                <span class="badge bg-secondary">Cancelled</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $swapRequest->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $swapRequest->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td>{{ $swapRequest->manager_remark ?? '-' }}</td>
                                        <td>
                                            @if ($swapRequest->reviewer)
                                                {{ $swapRequest->reviewer->first_name }}
                                                {{ $swapRequest->reviewer->last_name }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($swapRequest->reviewed_at)
                                                {{ \Carbon\Carbon::parse($swapRequest->reviewed_at)->format('d/m/Y H:i') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center text-muted">No shift swap requests found !</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    const leaveHistoryToggle = document.getElementById('leaveHistoryToggle');
    const leaveHistoryBody = document.getElementById('leaveHistoryBody');
    const leaveHistoryIcon = document.getElementById('leaveHistoryIcon');
    const preferencesHistoryToggle = document.getElementById('preferencesHistoryToggle');
    const preferencesHistoryBody = document.getElementById('preferencesHistoryBody');
    const preferencesHistoryIcon = document.getElementById('preferencesHistoryIcon');
    const shiftswapHistoryToggle = document.getElementById('shiftswapHistoryToggle');
    const shiftswapHistoryBody = document.getElementById('shiftswapHistoryBody');
    const shiftswapHistoryIcon = document.getElementById('shiftswapHistoryIcon');

    leaveHistoryToggle.addEventListener('click', function () {
        leaveHistoryBody.classList.toggle('d-none');
        leaveHistoryIcon.classList.toggle('rotate');
    });

    if (preferencesHistoryToggle && preferencesHistoryBody && preferencesHistoryIcon) {
        preferencesHistoryToggle.addEventListener('click', function () {
            preferencesHistoryBody.classList.toggle('d-none');
            preferencesHistoryIcon.classList.toggle('rotate');
        });
    }

    if (shiftswapHistoryToggle && shiftswapHistoryBody && shiftswapHistoryIcon) {
        shiftswapHistoryToggle.addEventListener('click', function () {
            shiftswapHistoryBody.classList.toggle('d-none');
            shiftswapHistoryIcon.classList.toggle('rotate');
        });
    }
</script>

@endsection