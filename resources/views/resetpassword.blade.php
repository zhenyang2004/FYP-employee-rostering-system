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
                                <h3 class="auth-title">Reset Password</h3>
                                <p class="auth-subtitle">Please enter your new password below.</p>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger text-center">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('password.update') }}">
                                @csrf

                                <input type="hidden" name="token" value="{{ $token }}">

                                <div class="row mb-3">
                                    <label for="email" class="form-label">Email:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                        <input id="email" type="email" name="email" class="form-control auth-input @error('email') is-invalid @enderror" required value="{{ old('email', $email) }}" placeholder="Enter your registeredemail">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="password" class="form-label">New Password:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                        <input id="password" type="password" name="password" class="form-control auth-input @error('password') is-invalid @enderror" required placeholder="Enter new password">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control auth-input @error('password_confirmation') is-invalid @enderror" required placeholder="Confirm new password">
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary btn-auth">Reset Passowrd</button>
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