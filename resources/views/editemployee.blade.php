@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>Edit Employee List</h2>
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

            <div class="profile-panel">
                <div class="profile-panel-header">
                    <div class="profile-panel-title">
                        <div>
                            <i class="fa fa-pencil"></i>
                            <span>Edit Employee Role</span>
                        </div>
                    </div>

                    <div class="profile-panel-actions">
                        <button type="submit" form="editEmployeeForm" class="btn btn-primary" title="Save">
                            <i class="fa fa-save"></i>
                        </button>

                        <a href="{{ route('employeelist') }}" class="btn btn-danger" title="Back">
                            <i class="fa fa-reply"></i>
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('employee.updateRole', $user->id) }}" id="editEmployeeForm" class="profile-form">
                    @csrf

                    <div class="profile-panel-body">

                        @if ($errors->any())
                            <div class="alert alert-danger m-3">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <div class="profile-form-row">
                            <label>Employee ID</label>
                            <input type="text" class="form-control" value="{{ $user->employee_id }}" disabled>
                        </div>

                        <div class="profile-form-row">
                            <label>First Name</label>
                            <input type="text" class="form-control" value="{{ $user->first_name }}" disabled>
                        </div>

                        <div class="profile-form-row">
                            <label>Last Name</label>
                            <input type="text" class="form-control" value="{{ $user->last_name }}" disabled>
                        </div>

                        <div class="profile-form-row">
                            <label>Status</label>
                            <input type="text" class="form-control" value="{{ $user->status }}" disabled>
                        </div>

                        <div class="profile-form-row">
                            <label>Email</label>
                            <input type="text" class="form-control" value="{{ $user->email }}" disabled>
                        </div>

                        <div class="profile-form-row">
                            <label>Phone Number</label>
                            <input type="text" class="form-control" value="{{ $user->phone_number }}" disabled>
                        </div>

                        <div class="profile-form-row">
                            <label>Role</label>
                            <select name="role" class="form-select">
                                <option value="Staff" {{ ($user->employee->role ?? '') == 'Staff' ? 'selected' : '' }}>
                                    Staff
                                </option>

                                <option value="Manager" {{ ($user->employee->role ?? '') == 'Manager' ? 'selected' : '' }}>
                                    Manager
                                </option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection