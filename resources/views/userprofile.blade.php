@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>Profile</h2>
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

            {{-- Profile form --}}
            <div class="profile-panel">
                <div class="profile-panel-header">
                    <div class="profile-panel-title">
                        <div><i class="bi bi-person-circle"></i>
                            <span>My Profile Information</span>
                        </div>
                    </div>

                    <a href="{{ url('/editprofile') }}" class="btn btn-primary" title="Edit Profile">
                        <i class="fa fa-pencil"></i>
                    </a>
                </div>

                <div class="profile-panel-body">

                    @if (session('success'))
                        <div class="alert alert-success m-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="profile-form-row">
                        <label>Profile Picture</label>

                        @if ($user->profile_pic)
                            <img src="{{ asset('storage/' . $user->profile_pic) }}" class="profile-preview-img" alt="Profile Picture">
                        @else
                            <div class="profile-preview-default">
                                <i class="bi bi-person-circle"></i>
                            </div>
                        @endif
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
                        <label>Employee ID</label>
                        <input type="text" class="form-control" value="{{ $user->employee_id }}" disabled>
                    </div>

                    <div class="profile-form-row">
                        <label>Phone Number</label>
                        <input type="text" class="form-control" value="{{ $user->phone_number }}" disabled>
                    </div>
                    
                    <div class="profile-form-row">
                        <label>IC Number</label>
                        <input type="text" class="form-control" value="{{ $user->ic_number }}" disabled>
                    </div>

                    <div class="profile-form-row">
                        <label>Email</label>
                        <input type="text" class="form-control" value="{{ $user->email }}" disabled>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection