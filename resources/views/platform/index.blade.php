@extends('layouts.app')

@section('title', 'Platform Dashboard')
@section('page-title', 'Platform Dashboard')

@push('styles')
<style>
.platform-hero {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    border-radius: 18px;
    color: white;
    padding: 24px 28px;
    margin-bottom: 24px;
}
.stat-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
    transition: transform .15s ease;
}
.stat-card:hover { transform: translateY(-2px); }
.stat-card .label {
    font-size: .72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em;
    color: #64748b; margin-bottom: 6px;
}
.stat-card .value { font-size: 1.6rem; font-weight: 800; color: #0f172a; }
.stat-card .sub { font-size: .78rem; color: #64748b; margin-top: 4px; }

.badge-trial    { background: rgba(245,158,11,.12); color: #b45309; }
.badge-active   { background: rgba(16,185,129,.12); color: #047857; }
.badge-expired  { background: rgba(239,68,68,.12); color: #b91c1c; }
.badge-suspended{ background: rgba(100,116,139,.12); color: #475569; }
.tenant-row td { vertical-align: middle; }
.btn-action { font-size: .72rem; padding: 2px 8px; }
</style>
@endpush

@section('content')

{{-- Flash messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Hero --}}
<div class="platform-hero shadow">
    <div class="d-flex align-items-center gap-3">
        <i class="bi bi-shield-lock-fill" style="font-size: 2rem;"></i>
        <div>
            <h4 class="mb-1 fw-bold">Platform Dashboard</h4>
            <p class="mb-0 opacity-75" style="font-size: .9rem;">
                Overview of all companies, users, and activity across WingPOS.
            </p>
        </div>
        <div class="ms-auto text-end d-none d-md-block">
            <div class="opacity-75 small">As of</div>
            <div class="fw-semibold">{{ now()->format('d M Y, H:i') }}</div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════
     USER ACTIVITY STATS
═══════════════════════════════════════ --}}
<h6 class="text-uppercase fw-bold text-muted mb-3" style="font-size:.78rem; letter-spacing:.05em;">
    <i class="bi bi-people-fill me-1"></i> User Activity
</h6>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3 col-lg">
        <div class="stat-card">
            <div class="label">Active in 24h</div>
            <div class="value text-primary">{{ number_format($usersActive24h) }}</div>
            <div class="sub">Logged in last 24 hours</div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-lg">
        <div class="stat-card">
            <div class="label">Active in 7d</div>
            <div class="value text-info">{{ number_format($usersActive7d) }}</div>
            <div class="sub">Logged in last 7 days</div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-lg">
        <div class="stat-card">
            <div class="label">Active in 30d</div>
            <div class="value" style="color:#7c3aed;">{{ number_format($usersActive30d) }}</div>
            <div class="sub">Logged in last 30 days</div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-lg">
        <div class="stat-card">
            <div class="label">Enabled accounts</div>
            <div class="value text-success">{{ number_format($usersAccountActive) }}</div>
            <div class="sub">is_active = true</div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-lg">
        <div class="stat-card">
            <div class="label">Total users</div>
            <div class="value">{{ number_format($usersTotal) }}</div>
            <div class="sub">All accounts</div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════
     COMPANY STATS
═══════════════════════════════════════ --}}
<h6 class="text-uppercase fw-bold text-muted mb-3" style="font-size:.78rem; letter-spacing:.05em;">
    <i class="bi bi-building me-1"></i> Companies
</h6>
<div class="row g-3 mb-4">
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="label">Total</div>
            <div class="value">{{ number_format($companiesTotal) }}</div>
            <div class="sub">Signed up</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="label">On Trial</div>
            <div class="value text-warning">{{ number_format($companiesOnTrial) }}</div>
            <div class="sub">Free 7-day trial</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="label">Active (Paid)</div>
            <div class="value text-success">{{ number_format($companiesActive) }}</div>
            <div class="sub">Paying subscribers</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="label">Expired</div>
            <div class="value text-danger">{{ number_format($companiesExpired) }}</div>
            <div class="sub">Need renewal</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card">
            <div class="label">Disabled</div>
            <div class="value text-muted">{{ number_format($companiesInactive) }}</div>
            <div class="sub">Suspended by admin</div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════
     ALL COMPANIES TABLE
═══════════════════════════════════════ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom-0 pt-3 pb-2 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-1"></i> All Companies</h6>
        <span class="badge bg-light text-dark border">{{ $companies->count() }} total</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th>Shop</th>
                    <th>Owner</th>
                    <th class="text-center">Users</th>
                    <th class="text-center">Branches</th>
                    <th class="text-center">Status</th>
                    <th>Trial / Expires</th>
                    <th>Last Activity</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $c)
                    <tr class="tenant-row">
                        <td>
                            <div class="fw-semibold">{{ $c['name'] }}</div>
                            <small class="text-muted">{{ $c['slug'] }}</small>
                            @if(!$c['is_active'])
                                <span class="badge bg-danger ms-1" style="font-size:.65rem;">DISABLED</span>
                            @endif
                        </td>
                        <td>
                            <div>{{ $c['owner_name'] }}</div>
                            <small class="text-muted">{{ $c['owner_email'] }}</small>
                        </td>
                        <td class="text-center">{{ $c['users_count'] }}</td>
                        <td class="text-center">{{ $c['branches_count'] }}</td>
                        <td class="text-center">
                            @php
                                $cls = match($c['subscription_status']) {
                                    'trial'     => 'badge-trial',
                                    'active'    => 'badge-active',
                                    'expired'   => 'badge-expired',
                                    'suspended' => 'badge-suspended',
                                    default     => 'badge-suspended',
                                };
                            @endphp
                            <span class="badge {{ $cls }} fw-semibold" style="font-size:.72rem;">
                                {{ strtoupper($c['subscription_status']) }}
                            </span>
                        </td>
                        <td>
                            @if($c['subscription_status'] === 'trial' && $c['trial_ends_at'])
                                <small>Trial ends<br><strong>{{ $c['trial_ends_at']->format('d M Y') }}</strong></small>
                            @elseif($c['expires_at'])
                                <small>Expires<br><strong>{{ $c['expires_at']->format('d M Y') }}</strong></small>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                        <td>
                            @if($c['last_activity'])
                                <small>{{ $c['last_activity']->diffForHumans() }}</small>
                            @else
                                <small class="text-muted">Never</small>
                            @endif
                        </td>
                        <td><small>{{ $c['created_at']->format('d M Y') }}</small></td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                {{-- Activate button: shown when suspended, expired, or trial --}}
                                @if(in_array($c['subscription_status'], ['suspended', 'expired', 'trial']))
                                    <button class="btn btn-sm btn-success btn-action"
                                        data-bs-toggle="modal"
                                        data-bs-target="#activateModal"
                                        data-company-id="{{ $c['id'] }}"
                                        data-company-name="{{ $c['name'] }}">
                                        Activate
                                    </button>
                                @endif
                                {{-- Suspend button: shown when active or trial --}}
                                @if(in_array($c['subscription_status'], ['active', 'trial']))
                                    <form method="POST"
                                        action="{{ route('platform.companies.suspend', $c['id']) }}"
                                        onsubmit="return confirm('Suspend {{ addslashes($c['name']) }}?')">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger btn-action">Suspend</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No companies yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Activate Modal --}}
<div class="modal fade" id="activateModal" tabindex="-1" aria-labelledby="activateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="activateModalLabel">Activate Company</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="activateForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <p class="mb-3 text-muted" style="font-size:.85rem;">
                        Activating <strong id="activateCompanyName"></strong>. Set the subscription expiry date.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.82rem;">Expires On</label>
                        <input type="date" name="expires_at" class="form-control form-control-sm"
                            min="{{ now()->addDay()->format('Y-m-d') }}"
                            value="{{ now()->addMonths(1)->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success">Activate</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('activateModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('activateCompanyName').textContent = btn.dataset.companyName;
    document.getElementById('activateForm').action =
        '/platform/companies/' + btn.dataset.companyId + '/activate';
});
</script>
@endpush

@endsection