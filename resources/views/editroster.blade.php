@extends('layouts.page')

@section('content')

@include('layouts.topbar')

<div class="dashboard-layout">
    @include('layouts.sidebar')

    <div class="dashboard-content">

        <div class="dashboard-page-header">
            <h2>Edit Roster</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb dashboard-breadcrumb">
                    @foreach ($breadcrumbs as $breadcrumb)
                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}" @if ($loop->last) aria-current="page" @endif>
                            @if (!$loop->last)
                                <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['text'] }}</a>
                            @else
                                {{ $breadcrumb['text'] }}
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        </div>

        <div class="employee-list-panel">
            <div class="employee-list-panel-header">
                <div class="employee-list-panel-title">
                    <div>
                        <i class="fa fa-pencil"></i>
                        <span>Edit Weekly Roster</span>
                    </div>
                </div>

                <a href="{{ route('viewroster', ['roster_id' => $roster->id]) }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-reply"></i>
                    Back
                </a>
            </div>

            <div class="employee-list-panel-body">

                @if (session('success'))
                    <div class="alert alert-success m-3">
                        <i class="fa fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger m-3">
                        <i class="fa fa-exclamation-circle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="roster-week-info m-3">
                    <strong>Roster Period:</strong>
                    {{ \Carbon\Carbon::parse($roster->start_date)->format('d/m/Y') }}
                    -
                    {{ \Carbon\Carbon::parse($roster->end_date)->format('d/m/Y') }}
                </div>

                @if ($understaffedShifts->count() > 0)
                    <div class="alert alert-warning m-3">
                        <i class="fa fa-exclamation-triangle"></i>
                        This roster has {{ $understaffedShifts->count() }} understaffed shift(s). You can add available staff to the understaffed shifts below.
                    </div>
                @endif

                <div class="table-responsive m-3">
                    <table class="table table-bordered employee-table roster-view-table">
                        <thead>
                            <tr>
                                <th>Shift</th>

                                @foreach ($dates as $date)
                                    <th>
                                        {{ $date->format('l') }}
                                        <br>
                                        <span>{{ $date->format('d/m/Y') }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($shiftTypes as $shiftType => $shiftInfo)
                                <tr>
                                    <td class="roster-shift-label">
                                        <strong>{{ $shiftInfo['label'] }}</strong>
                                        <br>
                                        <small>{{ $shiftInfo['time'] }}</small>
                                    </td>

                                    @foreach ($dates as $date)

                                        @php
                                            $dateKey = $date->format('Y-m-d');
                                            $assignedDetails = $groupedRoster[$dateKey][$shiftType] ?? [];
                                            $requirement = $groupedRequirements[$dateKey][$shiftType] ?? null;
                                            $staffOptions = $availableStaff[$dateKey][$shiftType] ?? collect();
                                        @endphp

                                        <td>
                                            @if (count($assignedDetails) > 0)
                                                @foreach ($assignedDetails as $detail)
                                                    <div class="roster-employee-chip edit-roster-chip">
                                                        <form method="POST" action="{{ route('editroster.removeStaff', $roster->id) }}" class="roster-remove-form" onsubmit="return confirm('Are you sure you want to remove this staff from the roster?');">
                                                            @csrf

                                                            <input type="hidden" name="roster_detail_id" value="{{ $detail->id }}">
                                                            <button type="submit" class="roster-remove-btn" title="Remove staff">
                                                                <i class="fa fa-times"></i>
                                                            </button>
                                                        </form>

                                                        <div class="roster-employee-name">
                                                            {{ $detail->user->first_name ?? '' }}
                                                            {{ $detail->user->last_name ?? '' }}
                                                        </div>

                                                        <div class="roster-employee-id">
                                                            {{ $detail->user->employee_id ?? '-' }}
                                                        </div>

                                                        @if ($detail->preference_result)
                                                            <div class="roster-preference-result">
                                                                {{ $detail->preference_result }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif

                                            @if ($requirement)
                                                @php
                                                    $isFilled = $requirement->assigned_staff >= $requirement->required_staff;
                                                @endphp

                                                <div class="roster-shift-status mt-2">
                                                    <small class="text-muted">
                                                        Required: {{ $requirement->required_staff }}
                                                        |
                                                        Assigned: {{ $requirement->assigned_staff }}
                                                    </small>
                                                    <br>

                                                    @if ($isFilled)
                                                        <span class="badge bg-success mt-1">Filled</span>
                                                    @else
                                                        <span class="badge bg-danger mt-1">Understaffed</span>
                                                    @endif
                                                </div>

                                                @if (!$isFilled)
                                                    <form method="POST" action="{{ route('editroster.addStaff', $roster->id) }}" class="mt-2">
                                                        @csrf

                                                        <input type="hidden" name="roster_shift_requirement_id" value="{{ $requirement->id }}">

                                                        <select name="user_id" class="form-select form-select-sm mb-2" required>
                                                            <option value="">Select available staff</option>

                                                            @foreach ($staffOptions as $staff)
                                                                <option value="{{ $staff->id }}">
                                                                    {{ $staff->first_name }} {{ $staff->last_name }}
                                                                    ({{ $staff->employee_id }})
                                                                </option>
                                                            @endforeach
                                                        </select>

                                                        @if ($staffOptions->count() > 0)
                                                            <button type="submit" class="btn btn-primary btn-sm">
                                                                <i class="fa fa-plus"></i>
                                                                Add
                                                            </button>
                                                        @else
                                                            <small class="text-muted">No available staff</small>
                                                        @endif
                                                    </form>
                                                @endif
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection