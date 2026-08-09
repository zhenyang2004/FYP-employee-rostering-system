@extends('layouts.page')

@section('content')

<div class="dashboard-page">

    @include('layouts.topbar')

    <div class="dashboard-layout">
        @include('layouts.sidebar')
        
        <div class="dashboard-content">
            {{-- Breadcrumb --}}
            <div class="dashboard-page-header">
                <h2>Edit Profile</h2>
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
                        <div><i class="bi bi-pencil-square"></i>
                            <span>Edit Profile</span>
                        </div>
                    </div>

                    <div class="profile-panel-actions">
                        <button type="submit" form="profileForm" class="btn btn-primary" title="Save">
                            <i class="fa fa-save"></i>
                        </button>

                        <a href="{{ url('/userprofile') }}" class="btn btn-danger" title="Back">
                            <i class="fa fa-reply"></i>
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('userprofile.update') }}" id="profileForm" class="profile-form" enctype="multipart/form-data">
                    @csrf

                    <div class="profile-panel-body">

                        @if ($errors->any())
                            <div class="alert alert-danger m-3">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <div class="profile-form-row profile-picture-row">
                            <label>Profile Picture</label>

                            <div class="profile-picture-upload">

                                {{-- Preview Image --}}
                                @if ($user->profile_pic)
                                    <img src="{{ asset('storage/' . $user->profile_pic) }}" class="profile-preview-img" id="profilePreviewImage" alt="Profile Picture">

                                    <button type="submit" form="profileForm" name="remove_profile_pic" class="btn btn-danger profile-picture-remove-btn" value="1" title="Remove Profile Picture">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                @else
                                    <img src="" class="profile-preview-img d-none" id="profilePreviewImage" alt="Profile Picture">
                                @endif

                                {{-- Default Avatar --}}
                                <div class="profile-preview-default {{ $user->profile_pic ? 'd-none' : '' }}" id="profileDefaultAvatar">
                                    <i class="bi bi-person-circle"></i>
                                </div>

                                <input type="file" name="profile_pic" id="profilePicInput" class="form-control profile-picture-input">
                            </div>
                        </div>

                        <div class="profile-form-row">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}">
                        </div>

                        <div class="profile-form-row">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}">
                        </div>

                        <div class="profile-form-row">
                            <label>Employee ID</label>
                            <input type="text" name="employee_id" class="form-control" value="{{ old('employee_id', $user->employee_id) }}">
                        </div>

                        <div class="profile-form-row">
                            <label>Phone Number</label>
                            <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $user->phone_number) }}">
                        </div>
                    
                        <div class="profile-form-row">
                            <label>IC Number</label>
                            <input type="text" name="ic_number" class="form-control" value="{{ old('ic_number', $user->ic_number) }}">
                        </div>

                        <div class="profile-form-row">
                            <label>Email</label>
                            <input type="text" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
    const profilePicInput = document.getElementById('profilePicInput');
    const profilePreviewImage = document.getElementById('profilePreviewImage');
    const profileDefaultAvatar = document.getElementById('profileDefaultAvatar');

    if(profilePicInput) {
        profilePicInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                const imageUrl = URL.createObjectURL(file);
                
                profilePreviewImage.src = imageUrl;
                profilePreviewImage.classList.remove('d-none');
                
                if (profileDefaultAvatar) {
                    profileDefaultAvatar.classList.add('d-none');
                }
            }
        });
    }
</script>

@endsection