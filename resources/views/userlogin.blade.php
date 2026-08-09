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
                            <h3 class="auth-title">User Login</h3>
                            <p class="auth-subtitle">Welcome back! Please login to continue.</p>
                        </div>

                        @if (session('success'))
                            <div id="success-alert" class="alert alert-success text-center">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger text-center">
                                {{ $errors->first() }}
                            </div>
                        @endif
                    
                        <form method="POST" action="{{ route('user.login') }}">
                            @csrf
                            <div class="row mb-3">
                                <label for="email" class="form-label">Email:</label>

                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                    <input id="email" type="email" name="email" class="form-control auth-input @error('email') is-invalid @enderror" required value="{{ old('email') }}" placeholder="Enter your email">
                                </div>

                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                
                            </div>

                            <div class="row mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                    <input id="password" type="password" name="password" class="form-control auth-input @error('password') is-invalid @enderror" required placeholder="Enter your password">
                                </div>

                                @error('password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="auth-login-options mb-3">
                                <div class="form-check remember-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label" for="remember">
                                        Remember Me
                                    </label>
                                </div>

                                <a href="{{ url('forgotpassword') }}" class="auth-link">Forgot Password?</a>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-auth">Login</button>
                            </div>

                            <div class="text-center mt-4">
                                <span class="auth-text">Don't have an account?</span>
                                <a href="{{ url('usersignup') }}" class="auth-link">
                                    Register Now
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