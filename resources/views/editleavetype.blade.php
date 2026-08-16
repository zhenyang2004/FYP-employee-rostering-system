@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>Edit Leave Type</h2>
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

            {{-- Edit Leave Type Form --}}
            <div class="profile-panel mb-3">
                <div class="profile-panel-header">
                    <div class="profile-panel-title">
                        <div>
                            <i class="fa fa-pencil"></i>
                            <span>Edit Leave Type</span>
                        </div>
                    </div>

                    <div class="profile-panel-actions">

                        <button type="submit" form="leaveTypeForm" class="btn btn-primary" title="Save">
                            <i class="fa fa-save"></i>
                        </button>
                        
                        <a href="{{ url('/setting') }}" class="btn btn-danger" title="Back">
                            <i class="fa fa-reply"></i>
                        </a>
                    </div>
                </div>

                <div class="profile-panel-body">
                    
                    <form method="POST" action="{{ route('setting.leavetype.update', $leaveType->id) }}" id="leaveTypeForm" class="profile-form">
                        @csrf

                         @if ($errors->any())
                            <div class="alert alert-danger m-3" id="errorAlert">
                                <i class="fa fa-exclamation-circle"></i>
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <div class="profile-form-row">
                            <label>Leave Type</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $leaveType->name) }}" required>
                        </div>

                        <div class="profile-form-row">
                            <label>Entitlement Days</label>
                            <input type="number" name="entitlement_days" class="form-control" value="{{ old('entitlement_days', $leaveType->entitlement_days) }}" min="0" step="0.5" required>
                        </div>

                        <div class="profile-form-row">
                            <label>Status</label>
                            <select name="status" class="form-select" required>
                                <option value="Enabled" {{ old('status', $leaveType->status) == 'Enabled' ? 'selected' : '' }}>Enabled</option>
                                <option value="Disabled" {{ old('status', $leaveType->status) == 'Disabled' ? 'selected' : '' }}>Disabled</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection