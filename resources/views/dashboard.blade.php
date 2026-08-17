@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>Dashboard</h2>
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

            {{-- Leave Balance Summary --}}
            <div class="employee-list-panel mb-3">
                <div class="employee-list-panel-header">
                    <div class="employee-list-panel-title">
                        <div>
                            <i class="fa fa-calendar-check-o"></i>
                            <span>Leave Balance Summary</span>
                        </div>
                    </div>
                </div>

                <div class="employee-list-panel-body">
                    <div class="leave-summary-container">

                        @forelse ($leaveSummaries as $leaveSummary)
                            <div class="leave-summary-card">

                                <div class="leave-summary-title">
                                    {{ $leaveSummary['name'] }}
                                </div>

                                <div class="leave-summary-main">
                                    @if ($leaveSummary['is_unlimited'])
                                        N/A
                                        <span>no limit</span>
                                    @else
                                        {{ $leaveSummary['remaining_days'] }}
                                        <span>days left</span>
                                    @endif
                                </div>

                                <div class="leave-summary-details">
                                    <div>
                                        <span>Entitlement</span>
                                        <strong>
                                            @if ($leaveSummary['is_unlimited'])
                                                N/A
                                            @else
                                                {{ $leaveSummary['entitlement_days'] }}
                                            @endif
                                        </strong>
                                    </div>
                                    <div>
                                        <span>Used</span>
                                        <strong>{{ $leaveSummary['used_days'] }}</strong>
                                    </div>
                                    <div>
                                        <span>Pending</span>
                                        <strong>{{ $leaveSummary['pending_days'] }}</strong>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted">
                                No leave type records found.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection