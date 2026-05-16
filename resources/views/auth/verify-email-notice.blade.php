@extends('layouts.guest')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-envelope-check" style="font-size: 3rem; color: #3b82f6;"></i>
                    <h4 class="mt-3">Verify your email address</h4>
                    <p class="text-muted">
                        Thanks for signing up! Before getting started, please verify your email
                        by clicking the link we just sent to you. 
                    </p>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="alert alert-info">
                        <strong>Next steps:</strong>
                        <ul class="mb-0 text-start">
                            <li>Check your email (including spam folder)</li>
                            <li>Click the verification link</li>
                            <li>Log in to access your WingPOS dashboard</li>
                            <li>Your 7-day free trial will start immediately</li>
                        </ul>
                    </div>

                    <p class="text-muted small">
                        Didn't receive the email? Check your spam folder or contact support.
                    </p>

                    <a href="{{ route('login') }}" class="btn btn-primary">
                        Return to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
