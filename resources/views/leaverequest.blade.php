@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>Leave Request</h2>
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

            {{-- Leave Request Form --}}
            <div class="profile-panel mb-3">
                <div class="profile-panel-header">
                    <div class="profile-panel-title">
                        <div>
                            <i class="fa fa-calendar-check-o"></i>
                            <span>Leave Request</span>
                        </div>
                    </div>
                </div>

                <div class="profile-panel-body">
                    <form method="POST" action="{{ route('leaverequest.store') }}" id="leaveRequestForm" class="profile-form" enctype="multipart/form-data">
                        @csrf

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

                        <div id="leaveAlert" class="alert alert-danger d-none m-3" role="alert">
                            <i class="fa fa-exclamation-circle"></i>
                            <span id="leaveAlertMessage"></span>
                        </div>

                        <div class="profile-form-row">
                            <label>Employee Name</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->first_name . ' ' . auth()->user()->last_name }}" readonly>
                        </div>

                        <div class="profile-form-row">
                            <label>Employee ID</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->employee_id }}" readonly>
                        </div>

                        <div class="profile-form-row">
                            <label>Leave Type <span class="text-danger">*</span></label>
                            <select name="leave_type_id" class="form-select" required>
                                <option value="">-- Select Leave Type --</option>
                                    
                                @foreach ($leaveTypes as $leaveType)
                                    <option value="{{ $leaveType->id }}" {{ old('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                                        {{ $leaveType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="profile-form-row">
                            <label>Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="startDate" class="form-control" value="{{ old('start_date') }}" required>
                        </div>

                        <div class="profile-form-row">
                            <label>End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="endDate" class="form-control" value="{{ old('end_date') }}" required>
                        </div>

                        <div class="profile-form-row">
                            <label>Total Days</label>
                            <input type="text" name="total_days" id="totalDays" class="form-control" value="{{ old('total_days') }}" readonly>
                        </div>

                        <div class="profile-form-row leave-reason-row">
                            <label>Reason</label>
                            <textarea name="reason" class="form-control leave-reason-textarea" rows="4" placeholder="Reason for leave request">{{ old('reason') }}</textarea>
                        </div>

                        <div class="profile-form-row leave-attachment-row">
                            <label>Attachment</label>
                            <div class="leave-attachment-field">
                                <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png,.jpeg">
                                <small class="text-muted">Upload supported document. Accepted formats: JPG, PNG, PDF.</small>
                            </div>
                        </div>

                        <div class="employee-filter-actions mt-3 leave-request-button-actions">
                            <button type="reset" class="btn btn-secondary btn-sm" id="resetLeaveBtn">
                                <i class="fa fa-refresh"></i>
                                Reset
                            </button>

                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa fa-paper-plane"></i>
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="employee-list-panel mt-3">
                <div class="employee-list-panel-header leave-history-toggle" id="leaveHistoryToggle">
                    <div class="employee-list-panel-title">
                        <div><i class="fa fa-history"></i>
                            <span>Leave History</span>
                        </div>
                    </div>

                    <div class="leave-history-toggle-icon">
                        <i class="fa fa-chevron-down" id="leaveHistoryIcon"></i>
                    </div>
                </div>

                <div class="employee-list-panel-body d-none" id="leaveHistoryBody">
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
                                        <td colspan="9" class="text-center text-muted">No leave requests found.</td>
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

{{-- Scripts --}}
<script>
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const totalDaysInput = document.getElementById('totalDays');
    const resetLeaveBtn = document.getElementById('resetLeaveBtn');
    const leaveAlert = document.getElementById('leaveAlert');
    const leaveAlertMessage = document.getElementById('leaveAlertMessage');
    const errorAlert = document.getElementById('errorAlert');
    const successAlert = document.getElementById('successAlert');
    const leaveHistoryToggle = document.getElementById('leaveHistoryToggle');
    const leaveHistoryBody = document.getElementById('leaveHistoryBody');
    const leaveHistoryIcon = document.getElementById('leaveHistoryIcon');

    const today = new Date();
    const todayFormatted = formatDate(today);

    startDateInput.addEventListener('change', calculateLeaveDays);
    endDateInput.addEventListener('change', calculateLeaveDays);

    function calculateLeaveDays() {
        const startDateValue = startDateInput.value;
        const endDateValue = endDateInput.value;

        hideLeaveAlert();
        
        if (!startDateValue || !endDateValue) {
            totalDaysInput.value = '';
            return;
        }

        const startDate = createDateFromInput(startDateValue);
        const endDate = createDateFromInput(endDateValue);

        if (endDate < startDate) {
            showLeaveAlert('End date cannot be earlier than start date.');

            endDateInput.value = '';
            totalDaysInput.value = '';

            return;
        }

        const totalDays = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;

        totalDaysInput.value = totalDays;
    }

    resetLeaveBtn.addEventListener('click', function () {
        totalDaysInput.value = '';
        hideLeaveAlert();
        hideServerAlerts();
    });

    function createDateFromInput(dateValue) {
        const dateParts = dateValue.split('-');
        
        return new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
    }

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    function showLeaveAlert(message) {
        leaveAlertMessage.textContent = message;
        leaveAlert.classList.remove('d-none');
    }

    function hideLeaveAlert() {
        leaveAlertMessage.textContent = '';
        leaveAlert.classList.add('d-none');
    }

    function hideServerAlerts() {
        if (errorAlert) {
            errorAlert.classList.add('d-none');
        }

        if (successAlert) {
            successAlert.classList.add('d-none');
        }
    }

    leaveHistoryToggle.addEventListener('click', function () {
        leaveHistoryBody.classList.toggle('d-none');
        leaveHistoryIcon.classList.toggle('rotate');
    });

    setTimeout(function () {
        hideServerAlerts();
    }, 4000);


</script>

@endsection