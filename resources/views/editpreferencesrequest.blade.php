@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>Edit Preferences</h2>
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

            {{-- Preferences Request Details --}}
            <div class="employee-list-panel mb-3">
                <div class="employee-list-panel-header">
                    <div class="employee-list-panel-title">
                        <div>
                            <i class="fa fa-pencil"></i>
                            <span>Edit Preferences Request</span>
                        </div>
                    </div>
                    <div class="employee-filter-actions mt-3">
                        <button type="submit" form="editPreferenceForm" class="btn btn-primary" title="Save">
                            <i class="fa fa-save"></i>
                        </button>

                        <a href="{{ route('preferencesrequest') }}" class="btn btn-danger" title="Back">
                            <i class="fa fa-reply"></i>
                        </a>
                    </div>
                </div>

                <div class="employee-list-panel-body">

                    <form method="POST" action="{{ route('editpreferencesrequest.update', $preferenceRequest->id) }}" id="editPreferenceForm" class="profile-form">
                        @csrf
                        
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <i class="fa fa-exclamation-circle"></i>
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Start Date</label>
                                <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($preferenceRequest->start_date)->format('d-m-Y') }}" disabled>
                            </div>
                
                            <div class="col-md-4">
                                <label class="form-label">End Date</label>
                                <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($preferenceRequest->end_date)->format('d-m-Y') }}" disabled>
                            </div>
                        </div>
                        <br>

                        <div class="table-responsive">
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

                                <tbody>
                                    @foreach ($preferenceRequest->preferences as $index => $preference)

                                        @php
                                            $preferenceType = old('preferences.' . $index . '.preference_type', $preference->preference_type);
                                            $shiftType = old('preferences.' . $index . '.shift_type', $preference->shift_type);
                                            $availableFrom = old('preferences.' . $index . '.available_from', $preference->available_from ? \Carbon\Carbon::parse($preference->available_from)->format('H:i') : '');
                                            $reason = old('preferences.' . $index . '.reason', $preference->reason);
                                        @endphp

                                        <tr>
                                            <td>
                                                {{ \Carbon\Carbon::parse($preference->preference_date)->format('l') }}
                                                <input type="hidden" name="preferences[{{ $index }}][id]" value="{{ $preference->id }}">
                                            </td>

                                            <td>
                                                {{ \Carbon\Carbon::parse($preference->preference_date)->format('Y-m-d') }}
                                                <input type="hidden" name="preferences[{{ $index }}][preference_date]" value="{{ $preference->preference_date }}">
                                            </td>

                                            <td>
                                                <select name="preferences[{{ $index }}][preference_type]" class="form-select preference-type" data-index="{{ $index }}" required>
                                                    <option value="">Select Preference</option>
                                                    <option value="Preferred Shift" {{ $preferenceType == 'Preferred Shift' ? 'selected' : '' }}>Preferred Shift</option>
                                                    <option value="Any Shift" {{ $preferenceType == 'Any Shift' ? 'selected' : '' }}>Any Shift</option>
                                                    <option value="Unavailable" {{ $preferenceType == 'Unavailable' ? 'selected' : '' }}>Unavailable</option>
                                                </select>
                                            </td>

                                            <td>
                                                <select name="preferences[{{ $index }}][shift_type]" class="form-select shift-type" id="shiftType{{ $index }}" {{ $preferenceType == 'Preferred Shift' ? '' : 'disabled' }}>
                                                    <option value="">Select Shift</option>
                                                    <option value="Morning Shift" {{ $shiftType == 'Morning Shift' ? 'selected' : '' }}>Morning Shift</option>
                                                    <option value="Afternoon Shift" {{ $shiftType == 'Afternoon Shift' ? 'selected' : '' }}>Afternoon Shift</option>
                                                    <option value="Night Shift" {{ $shiftType == 'Night Shift' ? 'selected' : '' }}>Night Shift</option>
                                                </select>
                                            </td>

                                            <td>
                                                <input type="time" name="preferences[{{ $index }}][available_from]" class="form-control available-from" id="availableFrom{{ $index }}" value="{{ $availableFrom }}" {{ $preferenceType == 'Any Shift' ? '' : 'disabled' }}>
                                            </td>

                                            <td>
                                                <input type="text" name="preferences[{{ $index }}][reason]" class="form-control" value="{{ $reason }}" placeholder="Reason optional">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const preferenceTypes = document.querySelectorAll('.preference-type');

    preferenceTypes.forEach(function (select) {
        select.addEventListener('change', function () {
            const index = this.dataset.index;

            const shiftType = document.getElementById('shiftType' + index);
            const availableFrom = document.getElementById('availableFrom' + index);

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
</script>

@endsection