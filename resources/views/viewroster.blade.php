@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>View Roster</h2>
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

            {{-- Select Roster Week --}}
            <div class="profile-panel mb-3">
                <div class="profile-panel-header">
                    <div class="profile-panel-title">
                        <div>
                            <i class="fa fa-calendar"></i>
                            <span>Select Roster Week</span>
                        </div>
                    </div>
                </div>

                <div class="profile-panel-body">
                    
                    <form method="GET" action="{{ route('viewroster') }}" class="profile-form">
                        
                        <div class="profile-form-row">
                            <label>Roster Week</label>
                            <select name="roster_id" class="form-select" onchange="this.form.submit()">

                                @forelse ($rosters as $roster)
                                    <option value="{{ $roster->id }}" {{ $selectedRoster && $selectedRoster->id == $roster->id ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::parse($roster->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($roster->end_date)->format('d/m/Y') }}
                                    </option>
                                @empty
                                    <option value="">No saved roster found</option>
                                @endforelse
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Weekly Roster --}}
            <div class="employee-list-panel">
                <div class="employee-list-panel-header">
                    <div class="employee-list-panel-title">
                        <div>
                            <i class="fa fa-table"></i>
                            <span>Weekly Roster</span>
                        </div>
                    </div>
                </div>

                <div class="employee-list-panel-body">
                    @if (!$selectedRoster) 
                        <div class="view-roster-empty">
                            <i class="fa fa-calendar-times-o"></i>
                            <div>
                                <h5>No Roster has been generated yet !</h5>
                                <p>Please generate and save a roster first.</p>
                            </div>
                        </div>
                    @else
                        <div class="roster-week-info">
                            <strong>Roster Period:</strong>
                            {{ \Carbon\Carbon::parse($selectedRoster->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($selectedRoster->end_date)->format('d/m/Y') }}
                        </div>

                        <div class="table-responsive">
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
                                                @endphp

                                                <td>
                                                    @if (count($assignedDetails) > 0)

                                                        @foreach ($assignedDetails as $detail)

                                                            @php 
                                                                $isCurrentUser = $detail->user_id == auth()->id();
                                                            @endphp

                                                            <div class="roster-employee-chip {{ $isCurrentUser ? 'current-user-shift' : '' }}">
                                                                <div class="roster-employee-name">
                                                                    {{ $detail->user->first_name ?? '' }}
                                                                    {{ $detail->user->last_name ?? '' }}

                                                                    @if ($isCurrentUser)
                                                                        <span class="current-user-badge">You</span>
                                                                    @endif
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
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection