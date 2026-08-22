@extends('layouts.page')

@section('content')

@include('layouts.topbar')

<div class="dashboard-layout">
    @include('layouts.sidebar')

    <div class="dashboard-content">

        <div class="dashboard-page-header">
            <h2>Employee List</h2>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb dashboard-breadcrumb">
                    @foreach ($breadcrumbs as $breadcrumb)
                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}"
                            @if ($loop->last) aria-current="page" @endif>
                            
                            @if (!$loop->last)
                                <a href="{{ $breadcrumb['url'] }}">
                                    {{ $breadcrumb['text'] }}
                                </a>
                            @else
                                {{ $breadcrumb['text'] }}
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        </div>

        <div class="employee-list-panel">
            <div class="employee-list-panel-header">
                <div class="employee-list-panel-title">
                    <div>
                        <i class="fa fa-lock"></i>
                        <span>Permission Denied</span>
                    </div>
                </div>
            </div>

            <div class="employee-list-panel-body">
                <div class="manage-permission-message">
                    <i class="fa fa-exclamation-circle"></i>

                    <div>
                        <h5>You do not have permission to access this page.</h5>
                        <p>This page is only available for Admin users.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection