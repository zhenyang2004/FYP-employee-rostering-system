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
                            <h3 class="auth-title">Forgot Password</h3>
                            <p class="auth-subtitle">Enter your email to receive reset link.</p>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success text-center">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger text-center">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf

                            <div class="row mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                    <input id="email" type="email" name="email" class="form-control auth-input @error('email') is-invalid @enderror" required value="{{ old('email') }}" placeholder="Enter your registered email">
                                </div>

                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-auth">Send Password Reset Link</button>
                            </div>

                            <div class="text-center mt-4">
                                <span class="auth-text">Remember your password?</span>
                                <a href="{{ url('userlogin') }}" class="auth-link">
                                    Back to Login
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