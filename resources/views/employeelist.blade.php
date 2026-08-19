@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>Employee List</h2>
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

                <form method="GET" action="{{ route('employeelist') }}" class="employee-filter-form">
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
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select">
                                    <option value="">All Roles</option>
                                    <option value="Staff" {{ request('role') == 'Staff' ? 'selected' : '' }}>Staff</option>
                                    <option value="Manager" {{ request('role') == 'Manager' ? 'selected' : '' }}>Manager</option>
                                </select>
                            </div>
                        </div>

                        <div class="employee-filter-actions">
                            <a href="{{ route('employeelist') }}" class="btn btn-secondary">
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
                    
            {{-- Employee List Table --}}
            <div class="employee-list-panel">
                <div class="employee-list-panel-header">
                    <div class="employee-list-panel-title">
                        <div><i class="fa fa-users"></i>
                            <span>Employee List</span>
                        </div>
                    </div>
                </div>

                <div class="employee-list-panel-body">

                    @if (session('success'))
                        <div class="alert alert-success m-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered employee-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Employee ID</th>
                                    <th>Full Name</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @if ($users->count() > 0)
                                    @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $user->employee_id }}</td>
                                        <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                                        <td>{{ $user->employee->role ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('editemployee', $user->id) }}" class="btn btn-primary btn-sm employee-action-btn" title="Edit">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            No employee records found.
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
