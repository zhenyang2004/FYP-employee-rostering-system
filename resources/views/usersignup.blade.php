@extends('layouts.page')

@section('content')

<div class="background">
    <div class="auth-container">
        <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12 col-sm-10 col-md-9 col-lg-6">

                <div class="auth-card card shadow border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h3 class="auth-title">User Register</h3>
                            <p class="auth-subtitle">Fill in your details to create a new account.</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 bg-red-100">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif                        

                        <form method="POST" action="{{ route('user.register') }}">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">First Name:</label>
                                    <input id="first_name" type="text" name="first_name" class="form-control auth-input" required value="{{ old('first_name') }}" placeholder="Enter your first name">
                                </div>

                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Last Name:</label>
                                    <input id="last_name" type="text" name="last_name" class="form-control auth-input" required value="{{ old('last_name') }}" placeholder="Enter your last name">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="employee_id" class="form-label">Employee ID:</label>
                                    <input id="employee_id" type="text" name="employee_id" class="form-control auth-input" required value="{{ old('employee_id') }}" placeholder="Enter your employee ID">
                                </div>

                                <div class="col-md-6">
                                    <label for="ic_number" class="form-label">IC Number:</label>
                                    <input id="ic_number" type="text" name="ic_number" class="form-control auth-input" required value="{{ old('ic_number') }}" placeholder="Enter your IC number">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="phone_number" class="form-label">Phone Number:</label>
                                    <input id="phone_number" type="text" name="phone_number" class="form-control auth-input" required value="{{ old('phone_number') }}" placeholder="Enter your phone number">
                                </div>
                    
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email:</label>
                                    <input id="email" type="email" name="email" class="form-control auth-input" required value="{{ old('email') }}" placeholder="Enter your email">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password:</label>
                                    <input id="password" type="password" name="password" class="form-control auth-input" required placeholder="Enter your password">
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label">Confirm Password:</label>
                                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control auth-input" required placeholder="Confirm your password">
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-auth">Register</button>
                            </div>

                            <div class="text-center mt-4">
                                <span class="auth-text">Already have an account?</span>
                                <a href="{{ url('userlogin') }}" class="auth-link">
                                    Login Here
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

@endsection