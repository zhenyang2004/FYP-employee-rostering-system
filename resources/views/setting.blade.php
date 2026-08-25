@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>Settings</h2>
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
                            <i class="fa fa-cog"></i>
                            <span>Settings</span>
                        </div>
                    </div>
                </div>

                <div class="settings-tabs">
                    <button type="button" class="settings-tab {{ session('active_tab', 'general') == 'general' ? 'active' : '' }}" data-tab="general">
                        General
                    </button>

                    <button type="button" class="settings-tab {{ session('active_tab', 'general') == 'leave-types' ? 'active' : '' }}" data-tab="leave-types">
                        Leave Types
                    </button>
                </div>

                <div class="settings-tab-content {{ session('active_tab', 'general') == 'general' ? 'active' : '' }}" id="general">
                    <div class="profile-panel settings-sub-panel">
                        <div class="profile-panel-header">
                            <div class="profile-panel-title">
                                <div>
                                    <i class="fa fa-calendar-check"></i>
                                    <span>Roster Rule Settings</span>
                                </div>
                            </div>
                        </div>

                        <div class="profile-panel-body">
                            <form method="POST" action="{{ route('setting.roster.update') }}" class="profile-form">
                                @csrf

                                @if (session('success') && session('active_tab') == 'general')
                                    <div class="alert alert-success m-3">
                                        <i class="fa fa-check-circle"></i>
                                        {{ session('success') }}
                                    </div>
                                @endif

                                @if ($errors->any() && session('active_tab') == 'general')
                                    <div class="alert alert-danger m-3">
                                        <i class="fa fa-exclamation-circle"></i>
                                        {{ $errors->first() }}
                                    </div>
                                @endif

                                <div class="profile-form-row">
                                    <label>Max Weekly Work Hours</label>
                                    <input type="number" name="max_weekly_hours" class="form-control" value="{{ old('max_weekly_hours', $rosterSetting->max_weekly_hours) }}" min="8" required>
                                </div>
                                
                                <div class="profile-form-row">
                                    <label>Shift Duration Hours</label>
                                    <input type="number" name="shift_duration_hours" class="form-control" value="{{ old('shift_duration_hours', $rosterSetting->shift_duration_hours) }}" min="1" required>
                                </div>

                                <div class="profile-form-row">
                                    <label>Morning Shift Time</label>
                                    <div class="shift-time-row">
                                        <input type="time" name="morning_start_time" class="form-control shift-time-input" value="{{ old('morning_start_time', \Carbon\Carbon::parse($rosterSetting->morning_start_time)->format('H:i')) }}" required>

                                        <span class="shift-time-separator">-</span>

                                        <input type="time" name="morning_end_time" class="form-control shift-time-input" value="{{ old('morning_end_time', \Carbon\Carbon::parse($rosterSetting->morning_end_time)->format('H:i')) }}" required>
                                    </div>
                                </div>

                                <div class="profile-form-row">
                                    <label>Afternoon Shift Time</label>
                                    <div class="shift-time-row">
                                        <input type="time" name="afternoon_start_time" class="form-control" value="{{ old('afternoon_start_time', \Carbon\Carbon::parse($rosterSetting->afternoon_start_time)->format('H:i')) }}" required>

                                            <span class="shift-time-separator">-</span>

                                        <input type="time" name="afternoon_end_time" class="form-control" value="{{ old('afternoon_end_time', \Carbon\Carbon::parse($rosterSetting->afternoon_end_time)->format('H:i')) }}" required>
                                    </div>
                                </div>

                                <div class="profile-form-row">
                                    <label>Night Shift Time</label>
                                    <div class="shift-time-row">
                                        <input type="time" name="night_start_time" class="form-control" value="{{ old('night_start_time', \Carbon\Carbon::parse($rosterSetting->night_start_time)->format('H:i')) }}" required>

                                        <span class="shift-time-separator">-</span>

                                        <input type="time" name="night_end_time" class="form-control" value="{{ old('night_end_time', \Carbon\Carbon::parse($rosterSetting->night_end_time)->format('H:i')) }}" required>
                                    </div>
                                </div>

                                <div class="settings-form-actions">
                                    <button type="reset" class="btn btn-secondary btn-sm">
                                        <i class="fa fa-refresh"></i>
                                        Reset
                                    </button>

                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fa fa-save"></i>
                                        Save Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                        
                {{-- Add Leave Type Form --}}
                <div class="settings-tab-content {{ session('active_tab', 'general') == 'leave-types' ? 'active' : '' }}" id="leave-types">
                    <div class="profile-panel settings-sub-panel">
                        <div class="profile-panel-header">
                            <div class="profile-panel-title">
                                <div>
                                    <i class="fa fa-plus-square"></i>
                                    <span>Add Leave Type</span>
                                </div>
                            </div>
                        </div>

                        <div class="profile-panel-body">
                            <form method="POST" action="{{ route('setting.leavetype.store') }}" id="leaveTypeForm" class="profile-form">
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

                                <div class="profile-form-row">
                                    <label>Leave Type</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Enter leave type name" required>
                                </div>

                                <div class="profile-form-row">
                                    <label>Entitlement Days</label>
                                    <input type="number" name="entitlement_days" class="form-control" value="{{ old('entitlement_days') }}" min="0" step="0.5"placeholder="Enter entitlement days" required>
                                </div>

                                <div class="settings-form-actions">
                                    <button type="reset" class="btn btn-secondary btn-sm">
                                        <i class="fa fa-refresh"></i>
                                        Reset
                                    </button>
                                    
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fa fa-save"></i>
                                        Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="employee-list-panel settings-sub-panel">
                        <div class="employee-list-panel-header">
                            <div class="employee-list-panel-title">
                                <div>
                                    <i class="fa fa-list"></i>
                                    <span>Leave Type List</span>
                                </div>
                            </div>
                        </div>

                        <div class="employee-list-panel-body">
                            <div class="table-responsive">
                                <table class="table table-bordered employee-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Leave Type</th>
                                            <th>Entitlement Days</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($leaveTypes as $index => $leaveType)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $leaveType->name }}</td>
                                                <td>{{ $leaveType->entitlement_days }}</td>
                                                <td>
                                                    @if ($leaveType->status == 'Enabled')
                                                        <span class="badge bg-success">Enabled</span>
                                                    @else
                                                        <span class="badge bg-secondary">Disabled</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ url('editleavetype', $leaveType->id) }}" class="btn btn-primary btn-sm employee-action-btn" title="Edit">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    
                                                    <form method="POST" action="{{ route('setting.leavetype.destroy', $leaveType->id) }}" onsubmit="return confirm('Are you sure you want to delete this leave type?');" style="display: inline-block;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">
                                                    No leave type records found.
                                                </td>
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
    </div>
</div>

<script>

    const settingsTabs = document.querySelectorAll('.settings-tab');
    const settingsContents = document.querySelectorAll('.settings-tab-content');

    settingsTabs.forEach(function(tab) {

        tab.addEventListener('click', function() {

            const targetTab = this.dataset.tab;

            settingsTabs.forEach(function(item) {
                item.classList.remove('active');
            });

            settingsContents.forEach(function(content) {
                content.classList.remove('active');
            });

            this.classList.add('active');

            document.getElementById(targetTab).classList.add('active');

        });

    });

</script>

@endsection