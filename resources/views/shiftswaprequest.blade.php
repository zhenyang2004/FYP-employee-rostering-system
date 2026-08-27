@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>Shift Swap Request</h2>
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

            {{-- Shift Swap Request Form --}}
            <div class="profile-panel mb-3">
                <div class="profile-panel-header">
                    <div class="profile-panel-title">
                        <div>
                            <i class="fa fa-exchange"></i>
                            <span>Shift Swap Request</span>
                        </div>
                    </div>
                </div>

                <div class="profile-panel-body">
                    <form method="POST" action="{{ route('shiftswaprequest.store') }}" id="shiftSwapRequestForm" class="profile-form">
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

                        <input type="hidden" name="roster_id" value="{{ $selectedRosterId }}">

                        <div class="profile-form-row">
                            <label>Roster Period</label>
                            <select name="roster_period_id" id="rosterPeriodSelect" class="form-select" required>
                                <option value="">-- Select Roster Period --</option>

                                @if ($rosters->count() == 0)
                                    <option value="">No available roster period.</option>
                                @else
                                    @foreach ($rosters as $roster)
                                        <option value="{{ route('shiftswaprequest', ['roster_id' => $roster->id]) }}"
                                            {{ $selectedRosterId == $roster->id ? 'selected' : '' }}>

                                            {{ \Carbon\Carbon::parse($roster->start_date)->format('d/m/Y') }}
                                            -
                                            {{ \Carbon\Carbon::parse($roster->end_date)->format('d/m/Y') }}

                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                                    
                        <div class="profile-form-row">
                            <label>My Shift</label>
                            <select name="requester_roster_detail_id" class="form-select" required>
                                <option value="">-- Select Your Shift --</option>
                                
                                @foreach ($myRosterDetails as $detail)
                                    <option value="{{ $detail->id }}" {{ old('requester_roster_detail_id') == $detail->id ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::parse($detail->roster_date)->format('d/m/Y') }}
                                        -
                                        {{ $detail->shift_type }}
                                        |
                                        {{ \Carbon\Carbon::parse($detail->shift_start_time)->format('H:i') }}
                                        -
                                        {{ \Carbon\Carbon::parse($detail->shift_end_time)->format('H:i') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="profile-form-row">
                            <label>Swap With Shift</label>
                            <select name="target_roster_detail_id" class="form-select" required>
                                <option value="">-- Select Another Employee Shift --</option>

                                @foreach ($targetRosterDetails as $detail)
                                    <option value="{{ $detail->id }}" {{ old('target_roster_detail_id') == $detail->id ? 'selected' : '' }}>
                                        {{ $detail->user->first_name ?? '' }}
                                        {{ $detail->user->last_name ?? '' }}
                                        |
                                        {{ \Carbon\Carbon::parse($detail->roster_date)->format('d/m/Y') }}
                                        -
                                        {{ $detail->shift_type }}
                                        |
                                        {{ \Carbon\Carbon::parse($detail->shift_start_time)->format('H:i') }}
                                        -
                                        {{ \Carbon\Carbon::parse($detail->shift_end_time)->format('H:i') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="profile-form-row">
                            <label>Reason</label>
                            <textarea name="reason" class="form-control leave-reason-textarea" rows="3" placeholder="Reason for shift swap">{{ old('reason') }}</textarea>
                        </div>

                        <div class="settings-form-actions">
                            <button type="reset" class="btn btn-secondary btn-sm">
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

            {{-- Table of shift swap requests I sent --}}
            <div class="profile-panel mb-3">
                <div class="profile-panel-header">
                    <div class="profile-panel-title">
                        <div>
                            <i class="fa fa-history"></i>
                            <span>Shift Swap Requests History</span>
                        </div>
                    </div>
                </div>

                <div class="employee-list-panel-body">
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered employee-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>My Shift</th>
                                    <th>Swap With</th>
                                    <th>Target Shift</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Submitted At</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($myShiftSwapRequests as $index => $swapRequest)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @if ($swapRequest->requesterRosterDetail)
                                                <strong>{{ \Carbon\Carbon::parse($swapRequest->requesterRosterDetail->roster_date)->format('d/m/Y') }}</strong>
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
                                        <td>{{ $swapRequest->targetUser->first_name }} {{ $swapRequest->targetUser->last_name }}</td>
                                        <td>
                                            @if ($swapRequest->targetRosterDetail)
                                                <strong>{{ \Carbon\Carbon::parse($swapRequest->targetRosterDetail->roster_date)->format('d/m/Y') }}</strong>
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
                                        <td>{{ $swapRequest->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No shift swap requests submitted.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Table of shift swap requests I received --}}
            <div class="profile-panel mb-3">
                <div class="profile-panel-header">
                    <div class="profile-panel-title">
                        <div>
                            <i class="fa fa-paper-plane"></i>
                            <span>Shift Swap Requests to Me</span>
                        </div>
                    </div>
                </div>

                <div class="employee-list-panel-body">
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered employee-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Requested By</th>
                                    <th>Their Shift</th>
                                    <th>My Shift</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($requestsToMe as $index => $swapRequest)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $swapRequest->requester->first_name }} {{ $swapRequest->requester->last_name }}</td>
                                        <td>
                                            @if ($swapRequest->requesterRosterDetail)
                                                <strong>{{ \Carbon\Carbon::parse($swapRequest->requesterRosterDetail->roster_date)->format('d/m/Y') }}</strong>
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
                                                <strong>{{ \Carbon\Carbon::parse($swapRequest->targetRosterDetail->roster_date)->format('d/m/Y') }}</strong>
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
                                            @if ($swapRequest->status == 'Pending Staff Approval')
                                                <form method="POST" action="{{ route('shiftswaprequest.accept', $swapRequest->id) }}" style="display: inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        Accept
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('shiftswaprequest.reject', $swapRequest->id) }}" style="display: inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        Reject
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted">Reviewed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No shift swap requests received.</td>
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
    const rosterPeriodSelect = document.getElementById('rosterPeriodSelect');

    if (rosterPeriodSelect) {
        rosterPeriodSelect.addEventListener('change', function() {
            if (this.value) {
                window.location.href = this.value;
            }
        });
    }
</script>

@endsection