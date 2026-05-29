@extends('layouts.app')

@section('title', 'System Control Panel')
@section('page-title', 'System Control')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3 border-0 text-center">
                    <h5 class="mb-0 fw-bold">System Status Toggle</h5>
                </div>
                <div class="card-body p-4 p-md-5 text-center">
                    @if($isSystemActive)
                        <div class="mb-4">
                            <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                                <i class="bi bi-broadcast fs-2"></i>
                            </div>
                            <h4 class="mt-3 fw-bold">System is ONLINE</h4>
                            <p class="text-muted small">All users can access the POS and Dashboard.</p>
                        </div>
                        <button type="button" class="btn btn-outline-danger rounded-pill px-4 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#deactivateSystemModal">
                            <i class="bi bi-power me-2"></i> Deactivate System
                        </button>
                    @else
                        <div class="mb-4">
                            <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                                <i class="bi bi-exclamation-octagon fs-2"></i>
                            </div>
                            <h4 class="mt-3 fw-bold">System is OFFLINE</h4>
                            <p class="text-muted small">Only you can access the system. Others are redirected.</p>
                        </div>
                        <button type="button" class="btn btn-outline-success rounded-pill px-4 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#activateSystemModal">
                            <i class="bi bi-play-fill me-2"></i> Activate System
                        </button>
                    @endif
                </div>
            </div>
        </div>

        @if($isSystemActive)
        <!-- Deactivation Modal -->
        <div class="modal fade" id="deactivateSystemModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content text-start">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-danger">Security Verification</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('system.toggle') }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="deactivate">
                        <div class="modal-body">
                            <p>You are about to deactivate the system. This will log out all other users. Are you sure you want to proceed?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger px-4">Confirm & Deactivate</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        @if(!$isSystemActive)
        <!-- Activation Modal -->
        <div class="modal fade" id="activateSystemModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content text-start">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-success">Security Verification</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('system.toggle') }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="activate">
                        <div class="modal-body">
                            <p>You are about to activate the system. All users will regain access. Are you sure you want to proceed?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success px-4">Confirm & Activate</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3 border-0 text-center">
                    <h5 class="mb-0 fw-bold">Subscription Management</h5>
                </div>
                <div class="card-body p-4 p-md-5 text-center">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show text-start" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show text-start" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="d-flex flex-column gap-3">
                        <button class="btn btn-success rounded-pill px-4 shadow-sm"
                                data-bs-toggle="modal" data-bs-target="#activateCompanyModal">
                            <i class="bi bi-check-circle me-2"></i> Activate
                            <span class="badge bg-white text-success ms-1">{{ $suspendedCompanies->count() }}</span>
                        </button>
                        <button class="btn btn-outline-danger rounded-pill px-4 shadow-sm"
                                data-bs-toggle="modal" data-bs-target="#deactivateCompanyModal">
                            <i class="bi bi-slash-circle me-2"></i> Deactivate
                            <span class="badge bg-danger text-white ms-1">{{ $activeCompanies->count() }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Activate Modal: lists suspended/expired companies --}}
    <div class="modal fade" id="activateCompanyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold text-success"><i class="bi bi-check-circle me-2"></i>Activate a Company</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    @if($suspendedCompanies->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">No suspended or expired companies.</p>
                    @else
                        <table class="table align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Company</th>
                                    <th>Owner</th>
                                    <th>Status</th>
                                    <th>Period</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($suspendedCompanies as $company)
                                <tr>
                                    <td class="ps-3 fw-semibold">{{ $company->name }}</td>
                                    <td class="text-muted">{{ $company->owner?->email ?? '—' }}</td>
                                    <td><span class="badge bg-danger">{{ ucfirst($company->subscription_status) }}</span></td>
                                    <td colspan="2">
                                        <form action="{{ route('system.company.manage') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="company_id" value="{{ $company->id }}">
                                            <input type="hidden" name="action" value="activate">
                                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                                <select name="period" class="form-select form-select-sm" style="width:120px"
                                                        onchange="toggleCustomActivate('{{ $company->id }}', this.value)">
                                                    <option value="30">30 days</option>
                                                    <option value="90">3 months</option>
                                                    <option value="180">6 months</option>
                                                    <option value="365" selected>1 year</option>
                                                    <option value="custom">Custom</option>
                                                </select>
                                                <input type="date" name="custom_date" id="customActivate{{ $company->id }}"
                                                       class="form-control form-control-sm d-none" style="width:140px"
                                                       min="{{ now()->addDay()->format('Y-m-d') }}">
                                                <button type="submit" class="btn btn-success btn-sm px-3">Activate</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Deactivate Modal: lists active/trial companies --}}
    <div class="modal fade" id="deactivateCompanyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold text-danger"><i class="bi bi-slash-circle me-2"></i>Deactivate a Company</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    @if($activeCompanies->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">No active companies.</p>
                    @else
                        <table class="table align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Company</th>
                                    <th>Owner</th>
                                    <th>Status</th>
                                    <th>Expires</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeCompanies as $company)
                                <tr>
                                    <td class="ps-3 fw-semibold">{{ $company->name }}</td>
                                    <td class="text-muted">{{ $company->owner?->email ?? '—' }}</td>
                                    <td><span class="badge bg-success">{{ ucfirst($company->subscription_status) }}</span></td>
                                    <td class="text-muted">
                                        {{ $company->subscription_expires_at ? $company->subscription_expires_at->format('d M Y') : '—' }}
                                    </td>
                                    <td>
                                        <form action="{{ route('system.company.manage') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="company_id" value="{{ $company->id }}">
                                            <input type="hidden" name="action" value="suspend">
                                            <input type="hidden" name="period" value="365">
                                            <button type="submit" class="btn btn-danger btn-sm px-3"
                                                    onclick="return confirm('Suspend {{ addslashes($company->name) }}?')">
                                                Suspend
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <div class="row justify-content-center mt-4">
        <div class="col-lg-12">
            <div class="p-4 bg-white shadow-sm rounded-4 small">
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i> Owner Security Information</h6>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="mb-0 text-muted">
                            <li>The <strong>Kill-Switch</strong> status is permanent until toggled back.</li>
                            <li>Your account ({{ auth()->user()->email }}) bypasses all restrictions.</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="mb-0 text-muted">
                            <li>Subscription expiry blocks all non-owner access automatically.</li>
                            <li>Audit logs will track all changes to these settings.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function toggleCustomActivate(id, value) {
    const el = document.getElementById('customActivate' + id);
    el.classList.toggle('d-none', value !== 'custom');
    el.required = value === 'custom';
}
</script>
@endsection
