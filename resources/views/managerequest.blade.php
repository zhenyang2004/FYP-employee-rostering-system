@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>Manage Request</h2>
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

            @if (!$hasPermission)
                <div class="employee-list-panel">

                    <div class="employee-list-panel-header">
                        <div class="employee-list-panel-title">
                            <div>
                                <i class="fa fa-lock"></i>
                                <span>Permission Denied</span>
                            </div>
                        </div>
                    </div>

                    <div class="employee-list-panel-body">
                        <div class="manage-permission-message">
                            <i class="fa fa-exclamation-circle"></i>
                            <div>
                                <h5>You do not have permission to access this page!</h5>
                                <p>Only managers are allowed to manage leave requests.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- Manage Leave Request --}}
                <div class="employee-list-panel">
                    <div class="employee-list-panel-header">
                        <div class="employee-list-panel-title">
                            <div>
                                <i class="fa fa-tasks"></i>
                                <span>Manage Leave Request</span>
                            </div>
                        </div>
                    </div>

                    <div class="employee-list-panel-body">
                        
                        @if (session('success'))
                            <div class="alert alert-success m-3" id="successAlert">
                                <i class="fa fa-check-circle"></i>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger m-3" id="errorAlert">
                                <i class="fa fa-exclamation-circle"></i>
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered employee-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Employee Name</th>
                                        <th>Employee ID</th>
                                        <th>Leave Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Total Days</th>
                                        <th>Reason</th>
                                        <th>Attachment</th>
                                        <th>Status</th>
                                        <th>Manager Remarks</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($leaveRequests as $index => $leaveRequest)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $leaveRequest->user->first_name }} {{ $leaveRequest->user->last_name }}</td>
                                            <td>{{ $leaveRequest->user->employee_id ?? '-' }}</td>
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
                                            <td>
                                                @if ($leaveRequest->user_id == auth()->id())
                                                    <span class="text-muted">Own request</span>
                                                @elseif ($leaveRequest->status == 'Pending')
                                                    <form method="POST" action="{{ route('managerequest.leave.status', $leaveRequest->id) }}" class="manage-request-action-form">
                                                        @csrf
                                                        
                                                        <textarea name="manager_remark" class="form-control manage-request-remarks" rows="2" placeholder="Manager Remarks"></textarea>

                                                        <div class="manage-request-action-buttons">
                                                            <button type="submit" class="btn btn-success btn-sm" name="status" value="Approved">
                                                                <i class="fa fa-check"></i>
                                                                Approve
                                                            </button>

                                                            <button type="submit" class="btn btn-danger btn-sm" name="status" value="Rejected">
                                                                <i class="fa fa-times"></i>
                                                                Reject
                                                            </button>
                                                        </div>
                                                    </form>
                                                @else
                                                    <span class="text-muted">Reviewed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center text-muted">No leave requests found.</td>
                                        </tr>
                                    @endforelse            
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Manage Shift Swap Request --}}
                <div class="employee-list-panel">
                    <div class="employee-list-panel-header">
                        <div class="employee-list-panel-title">
                            <div>
                                <i class="fa fa-exchange"></i>
                                <span>Manage Shift Swap Request</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection