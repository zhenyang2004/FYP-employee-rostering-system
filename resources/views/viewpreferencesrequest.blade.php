@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>View Preferences</h2>
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
                            <i class="fa fa-calendar"></i>
                            <span>Preferences Request Details</span>
                        </div>
                    </div>
                    <div class="employee-filter-actions mt-3">
                        <a href="{{ route('preferencesrequest') }}" class="btn btn-danger" title="Back">
                            <i class="fa fa-reply"></i>
                        </a>
                    </div>
                </div>

                <div class="employee-list-panel-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($preferenceRequest->start_date)->format('d-m-Y') }}" disabled>
                        </div>
                
                        <div class="col-md-4">
                            <label class="form-label">End Date</label>
                            <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($preferenceRequest->end_date)->format('d-m-Y') }}" disabled>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control" value="Submitted" disabled>
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
                                @if ($preferenceRequest->preferences->count() > 0)
                                    @foreach ($preferenceRequest->preferences as $preference)
                                        <tr>
                                            <td>
                                                {{ \Carbon\Carbon::parse($preference->preference_date)->format('l') }}
                                            </td>

                                            <td>
                                                {{ \Carbon\Carbon::parse($preference->preference_date)->format('Y-m-d') }}
                                            </td>

                                            <td>
                                                <select class="form-select" disabled>
                                                    <option selected>{{ $preference->preference_type }}</option>
                                                </select>
                                            </td>

                                            <td>
                                                <select class="form-select" disabled>
                                                    <option selected>{{ $preference->shift_type ?? 'Select Shift' }}</option>
                                                </select>
                                            </td>

                                            <td>
                                                <input type="time" class="form-control" value="{{ $preference->available_from ? \Carbon\Carbon::parse($preference->available_from)->format('H:i') : '' }}" disabled>
                                            </td>

                                            <td>
                                                <input type="text" class="form-control" value="{{ $preference->reason ?? '' }}" disabled>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            No preference details found.
                                        </td>
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

@endsection