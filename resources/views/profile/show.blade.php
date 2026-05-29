@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="container-fluid px-4 py-2">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('password_success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('password_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- LEFT COLUMN --}}
        <div class="col-lg-4">

            {{-- Account Summary Card --}}
            <div class="card mb-4">
                <div class="card-body text-center py-4">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-primary text-white fw-bold"
                         style="width:80px;height:80px;font-size:2rem;">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted small mb-2">{{ $user->email }}</p>
                    <span class="badge bg-primary">
                        @if($user->isSuperAdmin()) Super Admin
                        @elseif($user->isOwner()) Owner
                        @elseif($user->isManager()) Manager
                        @else Cashier
                        @endif
                    </span>
                    @if($user->is_active)
                        <span class="badge bg-success ms-1">Active</span>
                    @else
                        <span class="badge bg-danger ms-1">Inactive</span>
                    @endif
                </div>
                <ul class="list-group list-group-flush small">
                    @if($user->phone)
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-telephone me-2"></i>Phone</span>
                        <span>{{ $user->phone }}</span>
                    </li>
                    @endif
                    @if($user->branch)
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-building me-2"></i>Branch</span>
                        <span>{{ $user->branch->name }}</span>
                    </li>
                    @endif
                    @if($user->last_login_at)
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-clock me-2"></i>Last Login</span>
                        <span>{{ $user->last_login_at->diffForHumans() }}</span>
                    </li>
                    @endif
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-calendar me-2"></i>Member Since</span>
                        <span>{{ $user->created_at->format('M Y') }}</span>
                    </li>
                </ul>
            </div>

            {{-- Subscription Card --}}
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-semibold"><i class="bi bi-credit-card me-2 text-primary"></i>Subscription</h6>
                </div>
                <div class="card-body">
                    @php
                        $status = $company->subscription_status ?? 'pending';
                        $badgeClass = match($status) {
                            'active'    => 'bg-success',
                            'trial'     => 'bg-info',
                            'pending'   => 'bg-secondary',
                            'suspended' => 'bg-warning text-dark',
                            'expired'   => 'bg-danger',
                            default     => 'bg-secondary',
                        };
                        $label = match($status) {
                            'active'    => 'Active',
                            'trial'     => 'Free Trial',
                            'pending'   => 'Pending Verification',
                            'suspended' => 'Suspended',
                            'expired'   => 'Expired',
                            default     => ucfirst($status),
                        };
                    @endphp

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge {{ $badgeClass }} px-3 py-2">{{ $label }}</span>
                    </div>

                    @if($status === 'trial')
                        @php $daysLeft = $company->trialDaysRemaining(); @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Trial Period</span>
                                <span>{{ $daysLeft }} day{{ $daysLeft == 1 ? '' : 's' }} left</span>
                            </div>
                            <div class="progress" style="height:6px;">
                                @php $pct = max(0, min(100, round(($daysLeft / 7) * 100))); @endphp
                                <div class="progress-bar bg-info" style="width:{{ $pct }}%"></div>
                            </div>
                        </div>
                        @if($company->trial_ends_at)
                        <p class="small text-muted mb-3">
                            <i class="bi bi-calendar-event me-1"></i>
                            Trial ends: <strong>{{ $company->trial_ends_at->format('d M Y') }}</strong>
                        </p>
                        @endif
                    @endif

                    @if($status === 'active' && $company->subscription_expires_at)
                        <p class="small text-muted mb-3">
                            <i class="bi bi-calendar-check me-1"></i>
                            Renews / Expires: <strong>{{ $company->subscription_expires_at->format('d M Y') }}</strong>
                        </p>
                    @endif

                    @if(in_array($status, ['trial', 'pending', 'expired']))
                        <a href="{{ route('subscribe.plans') }}" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-arrow-up-circle me-1"></i>
                            {{ $status === 'expired' ? 'Renew Subscription' : 'Upgrade Now' }}
                        </a>
                    @elseif($status === 'active')
                        <a href="{{ route('subscribe.plans') }}" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-arrow-repeat me-1"></i>Manage Subscription
                        </a>
                    @endif
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-lg-8">

            {{-- Edit Profile Form --}}
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-semibold"><i class="bi bi-person me-2 text-primary"></i>Edit Profile</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control bg-light" value="{{ $user->email }}" disabled>
                            <small class="text-muted">Email cannot be changed.</small>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-floppy me-1"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Change Password Form --}}
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-semibold"><i class="bi bi-shield-lock me-2 text-primary"></i>Change Password</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password"
                                   class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-key me-1"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
