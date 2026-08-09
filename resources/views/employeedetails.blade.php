@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        
        {{-- Dashboard content --}}
        <div class="dashboard-content">
            <h2>Employees</h2>
            <p>Welcome back! Here is your employee management overview.</p>
        </div>
    </div>
</div>

@endsection