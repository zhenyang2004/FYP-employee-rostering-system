@extends('layouts.page')

@section('content')

@php
    $rosterPreviewSession = session('roster_preview');
@endphp

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>Generate Roster</h2>
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
                                <p>Only managers are allowed to generate the weekly rosters.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- Roster Period --}}
                <div class="employee-list-panel mb-3">
                    <div class="employee-list-panel-header">
                        <div class="employee-list-panel-title">
                            <div>
                                <i class="fa fa-calendar"></i>
                                <span>Roster Period</span>
                            </div>
                        </div>
                    </div>

                    <div class="employee-list-panel-body">

                        <form method="POST" action="{{  route('generateroster.preview') }}" id="generateRosterForm" class="profile-form">
                            @csrf
                            
                            @if (session('success'))
                                <div class="alert alert-success" id="successAlert">
                                    <i class="fa fa-check-circle"></i>
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger" id="errorAlert">
                                    <i class="fa fa-exclamation-circle"></i>
                                    {{ $errors->first() }}
                                </div>
                            @endif
                            
                            <div class="alert alert-danger d-none" id="rosterAlert" role="alert">
                                <i class="fa fa-exclamation-circle"></i>
                                <span id="rosterAlertMessage"></span>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" id="startDate" name="start_date" class="form-control" value="{{ old('start_date', $rosterPreviewSession['start_date'] ?? '') }}" required> 
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">End Date</label>
                                    <input type="date" id="endDate" name="end_date" class="form-control" value="{{ old('end_date', $rosterPreviewSession['end_date'] ?? '') }}" readonly>
                                </div>
                            </div>

                            {{--Daily Staff Requirement --}}
                            <div class="table-responsive mt-3">
                                <table class="table table-bordered employee-table">
                                    <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>Date</th>
                                            <th>Morning Required Staff</th>
                                            <th>Afternoon Required Staff</th>
                                            <th>Night Required Staff</th>
                                        </tr>
                                    </thead>

                                    <tbody id="requirementBody">
                                        @if ($rosterPreviewSession)
                                            @foreach ($rosterPreviewSession['requirements'] as $index => $requirement)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($requirement['roster_date'])->format('l') }}</td>
                                                    <td>
                                                        {{ \Carbon\Carbon::parse($requirement['roster_date'])->format('d/m/Y') }}
                                                        <input type="hidden" name="requirements[{{ $index }}][roster_date]" value="{{ $requirement['roster_date'] }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="requirements[{{ $index }}][morning_required]" class="form-control" value="{{ $requirement['morning_required'] }}" min="1" required> 
                                                    </td>
                                                    <td>
                                                        <input type="number" name="requirements[{{ $index }}][afternoon_required]" class="form-control" value="{{ $requirement['afternoon_required'] }}" min="1" required> 
                                                    </td>
                                                    <td>
                                                        <input type="number" name="requirements[{{ $index }}][night_required]" class="form-control" value="{{ $requirement['night_required'] }}" min="1" required> 
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">
                                                    Please select a start date to generate daily shift requirements.
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <div class="employee-filter-actions mt-3">
                                <button type="submit" class="btn btn-secondary" form="resetRosterForm" id="resetRosterBtn">
                                    <i class="fa fa-refresh"></i>
                                    Reset
                                </button>

                                <button type="submit" class="btn btn-primary" id="generateRosterBtn">
                                    <i class="fa fa-cogs"></i>
                                    Generate Roster
                                </button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('generateroster.reset') }}" id="resetRosterForm" class="profile-form" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>

                {{-- Scheduling Rules --}} 
                <div class="employee-list-panel">

                    <div class="employee-list-panel-header">
                        <div class="employee-list-panel-title">
                            <div>
                                <i class="fa fa-info-circle"></i>
                                <span>Scheduling Rules Applied</span>
                            </div>
                        </div>
                    </div>

                    <div class="employee-list-panel-body">
                        <ol class="mb-0">
                            <li>One employee only assigned to one shift per day.</li>
                            <li>Employees on approved leave or marked as Unavailable will not be assigned.</li>
                            <li>Employees who selected a preferred shift will be prioritised for their selected shift posiblity.</li>
                            <li>Any Shift employees will be used to fill remaining slots.</li>
                            <li>If multiple employees match the same shift, employees with fewer assigned shifts will be prioritised.</li>
                        </ol>
                    </div>
                </div>

                {{-- Weekly Roster Preview --}}
                @if ($rosterPreviewSession)

                    @php
                        $startDate = \Carbon\Carbon::parse($rosterPreviewSession['start_date']);
                        $weekDates = [];

                        for ($i = 0; $i < 7; $i++) {
                            $weekDates[] = $startDate->copy()->addDays($i);
                        }

                        $previewByShift = collect($rosterPreviewSession['roster_preview'])->groupBy('shift_type');

                        $shiftTypes = [
                            'Morning Shift',
                            'Afternoon Shift',
                            'Night Shift',
                        ];

                        $hasUnderstaffed = collect($rosterPreviewSession['roster_preview'])->contains(function ($shift) {
                            return $shift['status'] == 'Understaffed';                        
                        });
                    @endphp

                    <div class="employee-list-panel mb-3">
                        <div class="employee-list-panel-header">
                            <div class="employee-list-panel-title">
                                <div>
                                    <i class="fa fa-table"></i>
                                    <span>Weekly Roster Preview</span>
                                </div>
                            </div>
                        </div>

                        <div class="employee-list-panel-body">
                            <div class="mb-3">
                                <strong>Roster Period</strong>
                                {{ \Carbon\Carbon::parse($rosterPreviewSession['start_date'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($rosterPreviewSession['end_date'])->format('d/m/Y') }}
                            </div>

                            @if ($hasUnderstaffed)
                                <div class="alert alert-warning">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    This roster contains understaffed shifts. Please review the required staff or save the roster with understaffed status.
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-bordered roster-calendar-table">
                                    <thead>
                                        <tr>
                                            <th class="shift-column">Shift</th>
                                            @foreach ($weekDates as $date)
                                                <th class="text-center">
                                                    {{ $date->format('l') }}
                                                    <br>
                                                    <span class="text-muted">{{ $date->format('d/m') }}</span>
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($shiftTypes as $shiftType)

                                            @php
                                                $firstShift = $previewByShift[$shiftType][0] ?? null;
                                                $shiftTime = '-';

                                                if ($firstShift) {
                                                    $shiftTime = \Carbon\Carbon::parse($firstShift['shift_start_time'])->format('H:i') . ' - ' . \Carbon\Carbon::parse($firstShift['shift_end_time'])->format('H:i');
                                                }
                                            @endphp
                                            
                                            <tr>
                                                <td class="shift-cell">
                                                    {{ $shiftType }}
                                                    <span class="shift-time">{{ $shiftTime }}</span>
                                                </td>

                                                @foreach ($weekDates as $date)

                                                    @php
                                                        $shiftPreview = null;

                                                        foreach ($rosterPreviewSession['roster_preview'] as $item) {
                                                            if ($item['roster_date'] == $date->format('Y-m-d') && $item['shift_type'] == $shiftType) {
                                                                $shiftPreview = $item;
                                                                break;
                                                            }
                                                        }
                                                    @endphp

                                                    <td>
                                                        @if ($shiftPreview)
                                                            @if (count($shiftPreview['assigned_employees']) > 0)
                                                                @foreach ($shiftPreview['assigned_employees'] as $employee)
                                                                    
                                                                    @php
                                                                        $resultClass = 'bg-secondary';
                                                                        if ($employee['preference_result'] == 'Matched') {
                                                                            $resultClass = 'bg-success';
                                                                        }elseif ($employee['preference_result'] == 'Not Matched') {
                                                                            $resultClass = 'bg-warning text-dark';
                                                                        }elseif ($employee['preference_result'] == 'Assigned') {
                                                                            $resultClass = 'bg-info';
                                                                        }
                                                                    @endphp

                                                                    <div class="employee-chip">
                                                                        {{ $employee['employee_name'] }}

                                                                        <span class="preference-label">
                                                                            <span class="badge {{ $resultClass }}">{{ $employee['preference_result'] }}</span>
                                                                        </span>
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                <span class="text-muted">No employees assigned</span>
                                                            @endif

                                                            <div class="mt-2">
                                                                <small class="text-muted">
                                                                    Required: {{ $shiftPreview['required_staff'] }}
                                                                    |
                                                                    Assigned: {{ $shiftPreview['assigned_staff'] }}
                                                                </small>
                                                            </div>

                                                            @if ($shiftPreview['status'] == 'Filled')
                                                                <span class="badge bg-success mt-1">Filled</span>
                                                            @else
                                                                <span class="badge bg-danger mt-1">Understaffed</span>
                                                            @endif

                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="employee-filter-actions mt-3">
                                <form method="POST" action="{{ route('generateroster.save') }}">
                                    @csrf

                                    <button type="submit" class="btn {{ $hasUnderstaffed ? 'btn-warning' : 'btn-success' }}">
                                        <i class="fa fa-save"></i>
                                        {{ $hasUnderstaffed ? 'Save Roster with Understaffed Shifts' : 'Save Roster' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- Script --}}
<script>
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const requirementBody = document.getElementById('requirementBody');
    const resetRosterBtn = document.getElementById('resetRosterBtn');
    const rosterAlert = document.getElementById('rosterAlert');
    const rosterAlertMessage = document.getElementById('rosterAlertMessage');
    const errorAlert = document.getElementById('errorAlert');
    const successAlert = document.getElementById('successAlert');

    const today = new Date();
    const todayFormatted = formatDate(today);

    startDateInput.addEventListener('change', function () {
        const startDateValue = this.value;

        if (!startDateValue) {
            return;
        }

        if (startDateValue < todayFormatted) {
            showRosterAlert('Start date cannot be earlier than today.');

            startDateInput.value = '';
            endDateInput.value = '';

            requirementBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Please select a start date to generate daily shift requirements.
                    </td>
                </tr>
            `;

            return;
        }

        hideRosterAlert();
        hideServerAlerts();

        const startDate = createDateFromInput(startDateValue);
        const endDate = new Date(startDate);

        endDate.setDate(startDate.getDate() + 6);
        endDateInput.value = formatDate(endDate);

        generateRequirementTable(startDate);
    });

    function generateRequirementTable(startDate) {
        requirementBody.innerHTML = '';

        for (let i =0; i < 7; i++) {
            const currentDate = new Date(startDate);
            currentDate.setDate(startDate.getDate() + i);

            const dayName = getDayName(currentDate);
            const displayDate = formatDisplayDate(currentDate);
            const formattedDate = formatDate(currentDate);

            let morningDefault = 2;
            let afternoonDefault = 2;
            let nightDefault = 2;

            if (dayName === 'Friday' || dayName === 'Saturday' || dayName === 'Sunday') {
                morningDefault = 3;
                afternoonDefault = 3;
                nightDefault = 3;
            }

            const row = `
                <tr>
                    <td>${dayName}</td>
                    <td>
                        ${displayDate}
                        <input type="hidden" name="requirements[${i}][roster_date]" value="${formattedDate}">
                    </td>
                    <td>
                        <input type="number" name="requirements[${i}][morning_required]" class="form-control" value="${morningDefault}" min="1" required>
                    </td>
                    <td>
                        <input type="number" name="requirements[${i}][afternoon_required]" class="form-control" value="${afternoonDefault}" min="1" required>
                    </td>
                    <td>
                        <input type="number" name="requirements[${i}][night_required]" class="form-control" value="${nightDefault}" min="1" required>
                    </td>
                </tr>
            `;

            requirementBody.insertAdjacentHTML('beforeend', row);
        }
    }

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

    function formatDisplayDate(date) {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();

        return `${day}/${month}/${year}`;
    }

    function getDayName(date) {
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return dayNames[date.getDay()];
    }

    function showRosterAlert(message) {
        rosterAlertMessage.textContent = message;
        rosterAlert.classList.remove('d-none');
    }

    function hideRosterAlert() {
        rosterAlertMessage.textContent = '';
        rosterAlert.classList.add('d-none');
    }

    function hideServerAlerts() {
        if (errorAlert) {
            errorAlert.classList.add('d-none');
        }

        if (successAlert) {
            successAlert.classList.add('d-none');
        }
    }

    setTimeout(function () {
        hideServerAlerts();
    }, 4000);

</script>
@endsection