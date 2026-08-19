@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>Employee Details</h2>
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

            {{-- Filter panel --}}
            <div class="employee-filter-panel mb-3">
                <div class="employee-filter-header">
                    <div class="employee-filter-title">
                        <i class="fa fa-filter"></i>
                        <span>Filter</span>
                    </div>
                </div>

                <form method="GET" action="{{ route('employeedetails') }}" class="employee-filter-form">
                    <div class="employee-filter-body">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Employee ID</label>
                                <input type="text" name="employee_id" class="form-control" placeholder="Employee ID" value="{{ request('employee_id') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Full Name" value="{{ request('name') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">IC Number</label>
                                <input type="text" name="ic_number" class="form-control" placeholder="IC Number" value="{{ request('ic_number') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control" placeholder="Phone Number" value="{{ request('phone_number') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select">
                                    <option value="">All Roles</option>
                                    <option value="Staff" {{ request('role') == 'Staff' ? 'selected' : '' }}>Staff</option>
                                    <option value="Manager" {{ request('role') == 'Manager' ? 'selected' : '' }}>Manager</option>
                                </select>
                            </div>
                        </div>

                        <div class="employee-filter-actions">
                            <a href="{{ route('employeedetails') }}" class="btn btn-secondary">
                                <i class="fa fa-refresh"></i>
                                Reset
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-filter"></i>
                                Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Employee Details Cards --}}
            <div class="employee-card-panel">
                <div class="employee-card-panel-header">
                    <div class="employee-card-panel-title">
                        <div>
                            <i class="fa fa-users"></i>
                            <span>Employee Details</span>
                        </div>
                    </div>
                </div>
                
                <div class="employee-card-panel-body">
                    <div class="employee-card-grid">

                        @forelse ($users as $employee)
                            <div class="employee-profile-card">

                                {{-- Profile Picture --}}
                                <div class="employee-card-image-wrapper">
                                    @if ($employee->profile_pic)
                                        <img src="{{ asset('storage/' . $employee->profile_pic) }}" alt="Profile Picture" class="employee-card-image">
                                    @else
                                        <div class="employee-card-placeholder">
                                            <i class="bi bi-person-circle"></i>
                                        </div>
                                    @endif  
                                </div>

                                <div class="employee-card-name">
                                    {{ $employee->first_name }} {{ $employee->last_name }}
                                </div>

                                <div class="employee-card-id">
                                    {{ $employee->employee_id }}
                                </div>

                                <div class="employee-card-role">
                                    @if (optional($employee->employee)->role == 'Manager')
                                        <span class="badge bg-primary">Manager</span>
                                    @else
                                        <span class="badge bg-secondary">Staff</span>
                                    @endif
                                </div>

                                <div class="employee-card-info">
                                    <div class="employee-card-info-row">
                                        <i class="fa fa-envelope"></i>
                                        <span>{{ $employee->email }}</span>
                                    </div>

                                    <div class="employee-card-info-row">
                                        <i class="fa fa-phone"></i>
                                        <span>{{ $employee->phone_number }}</span>
                                    </div>

                                    <div class="employee-card-info-row">
                                        <i class="fa fa-id-card"></i>
                                        <span>{{ $employee->ic_number }}</span>
                                    </div>
                                </div>

                                <div class="employee-card-actions">
                                    <a href="#" class="employee-view-btn">
                                        <i class="fa fa-eye"></i>
                                        View
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="employee-card-empty">
                                <i class="fa fa-user-times"></i>
                                <div>
                                    <h5>No Employee Found!</h5>
                                    <p>Please filter and try again.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection