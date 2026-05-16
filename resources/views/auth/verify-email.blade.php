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
                        by clicking the link we just sent you. If you didn't receive it,
                        we'll happily send another.
                    </p>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            Resend verification email
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-link text-muted">Sign out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection