@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>Preferences Request</h2>
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

            {{-- Preferences Request Form --}}
            <div class="employee-list-panel mb-3">
                <div class="employee-list-panel-header">
                    <div class="employee-list-panel-title">
                        <div><i class="fa fa-sliders"></i>
                            <span>Weekly Preferences Request</span>
                        </div>
                    </div>
                </div>

                <div class="employee-list-panel-body">
                    <form method="POST" action="{{ route('preferencesrequest.store') }}">
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

                        <div class="alert alert-danger d-none" id="preferenceAlert" role="alert">
                            <i class="fa fa-exclamation-circle"></i>
                            <span id="preferenceAlertMessage"></span>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Start Date</label>
                                <input type="date" id="startDate" name="start_date" class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">End Date</label>
                                <input type="date" id="endDate" name="end_date" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-bordered employee-table">
                                <thead>
                                    <tr>
                                        <th>Day</th>
                                        <th>Date</th>
                                        <th>Preference Type</th>
                                        <th>Shift Types</th>
                                        <th>Available From</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>

                                <tbody id="weeklyPreferenceBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Please select a start date to generate the weekly preferences.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="employee-filter-actions mt-3">
                            <button type="reset" class="btn btn-secondary" id="resetPreferenceForm">
                                <i class="fa fa-refresh"></i>
                                Reset
                            </button>

                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-paper-plane"></i>
                                Submit
                            </button>
                        </div>
                    </form>
                </div>        
            </div>

            {{-- Employee Preferences Table --}}
            <div class="employee-list-panel mt-3">

                <div class="employee-list-panel-header">
                    <div class="employee-list-panel-title">
                        <div>
                            <i class="fa fa-history"></i>
                            <span>Preferences History</span>
                        </div>
                    </div>
                </div>

                <div class="employee-list-panel-body">
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
                                                 @php
                                                    $canEdit = \Carbon\Carbon::parse($preferenceRequest->start_date)->gt(\Carbon\Carbon::today());
                                                @endphp

                                                <a href="{{ url('/viewpreferencesrequest', $preferenceRequest->id) }}" class="btn btn-primary btn-sm employee-action-btn" title="View">
                                                    <i class="fa fa-eye"></i>
                                                </a>

                                                @if ($canEdit)
                                                    <a href="{{ url('/editpreferencesrequest', $preferenceRequest->id) }}" class="btn btn-primary btn-sm employee-action-btn" title="Edit">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-secondary btn-sm employee-action-btn" title="Edit is not allowed" disabled>
                                                        <i class="fa fa-pencil"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No preferences request found!</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script --}}
<script>
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const weeklyPreferenceBody = document.getElementById('weeklyPreferenceBody');
    const resetPreferenceForm = document.getElementById('resetPreferenceForm');
    const preferenceAlert = document.getElementById('preferenceAlert');
    const preferenceAlertMessage = document.getElementById('preferenceAlertMessage');
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
            showPreferenceAlert('Start date cannot be earlier than today.');
            startDateInput.value = '';
            endDateInput.value = '';

            weeklyPreferenceBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        Please select a start date to generate weekly preferences.
                    </td>
                </tr>
            `;

            return;
        }

        hidePreferenceAlert();

        const dateParts = startDateValue.split('-');

        const startDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
        const endDate = new Date(startDate);
        endDate.setDate(startDate.getDate() + 6);
        endDateInput.value = formatDate(endDate);
        generateWeeklyPreferenceForm(startDate);
    });

    function showPreferenceAlert(message) {
        preferenceAlertMessage.textContent = message;
        preferenceAlert.classList.remove('d-none');
    }

    function hidePreferenceAlert() {
        preferenceAlertMessage.textContent = '';
        preferenceAlert.classList.add('d-none');
    }

    function generateWeeklyPreferenceForm(startDate) {
        weeklyPreferenceBody.innerHTML = '';
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        for (let i = 0; i < 7; i++) {
            const currentDate = new Date(startDate);
            currentDate.setDate(startDate.getDate() + i);

            const formattedDate = formatDate(currentDate);
            const dayName = dayNames[currentDate.getDay()];

            const row = `
                <tr>
                    <td>
                        ${dayName}
                        <input type="hidden" name="preferences[${i}][day_name]" value="${dayName}">
                    </td>

                    <td>
                        ${formattedDate}
                        <input type="hidden" name="preferences[${i}][preference_date]" value="${formattedDate}">
                    </td>

                    <td>
                        <select name="preferences[${i}][preference_type]" class="form-select preference-type" data-index="${i}" required>
                            <option value="">Select Preference</option>
                            <option value="Preferred Shift">Preferred Shift</option>
                            <option value="Any Shift">Any Shift</option>
                            <option value="Unavailable">Unavailable</option>
                        </select>
                    </td>

                    <td>
                        <select name="preferences[${i}][shift_type]" class="form-select shift-type" id="shiftType${i}" disabled>
                            <option value="">Select Shift</option>
                            <option value="Morning Shift">Morning Shift</option>
                            <option value="Afternoon Shift">Afternoon Shift</option>
                            <option value="Night Shift">Night Shift</option>
                        </select>
                    </td>

                    <td>
                        <input type="time" name="preferences[${i}][available_from]" class="form-control available-from" id="availableFrom${i}" disabled>
                    </td>
                    
                    <td>
                        <input type="text" name="preferences[${i}][reason]" class="form-control" placeholder="Reason optional">
                    </td>
                </tr>
            `;

            weeklyPreferenceBody.insertAdjacentHTML('beforeend', row);
        }

        activePreferenceTypeChange();
    }

    function activePreferenceTypeChange() {
        const preferenceTypes = document.querySelectorAll('.preference-type');

        preferenceTypes.forEach(function (select) {
            select.addEventListener('change', function () {
                const index = this.dataset.index;
                const shiftType = document.getElementById('shiftType' + index);
                const availableFrom = document.getElementById('availableFrom' + index);

                //Reset first
                shiftType.value = '';
                availableFrom.value = '';

                shiftType.disabled = true;
                availableFrom.disabled = true;

                shiftType.required = false;
                availableFrom.required = false;

                if (this.value === 'Preferred Shift') {
                    shiftType.disabled = false;
                    shiftType.required = true;
                }

                if (this.value === 'Any Shift') {
                    availableFrom.disabled = false;
                    availableFrom.required = false;
                }
            });
        });
    }

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    resetPreferenceForm.addEventListener('click', function () {
        endDateInput.value = '';
        hidePreferenceAlert();

        weeklyPreferenceBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    Please select a start date to generate weekly preferences.
                </td>
            </tr>
        `;
    });

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