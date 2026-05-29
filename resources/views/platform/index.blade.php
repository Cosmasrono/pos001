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
    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-shield-lock-fill" style="font-size: 2rem;"></i>
            <div>
                <h4 class="mb-1 fw-bold">Platform Dashboard</h4>
                <p class="mb-0 opacity-75" style="font-size: .9rem;">
                    Overview of all companies, users, and activity across WingPOS.
                </p>
            </div>
        </div>
        <form method="POST" action="{{ route('platform.bots.purge') }}"
              onsubmit="return confirm('Delete all bot registrations (never logged in, no products)? This cannot be undone.')">
            @csrf
            <button type="submit" class="btn btn-sm btn-danger d-flex align-items-center gap-2">
                <i class="bi bi-trash3-fill"></i> Purge Bot Registrations
            </button>
        </form>
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
    <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-1"></i> All Companies</h6>
            <span class="badge bg-light text-dark border" id="companyCount">{{ $companies->count() }} shown</span>
        </div>
        {{-- Status filter --}}
        <div class="btn-group btn-group-sm flex-wrap" role="group" id="statusFilter">
            <button type="button" class="btn btn-dark active" data-filter="all">All</button>
            <button type="button" class="btn btn-outline-success" data-filter="active">Active</button>
            <button type="button" class="btn btn-outline-warning" data-filter="trial">Trial</button>
            <button type="button" class="btn btn-outline-danger" data-filter="expired">Expired</button>
            <button type="button" class="btn btn-outline-secondary" data-filter="suspended">Suspended</button>
        </div>
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
                    <tr class="tenant-row" data-status="{{ $c['subscription_status'] }}">
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
                                {{-- Activate for 1 year --}}
                                @if(in_array($c['subscription_status'], ['suspended', 'expired', 'trial']))
                                    <form method="POST"
                                          action="{{ route('platform.companies.activate', $c['id']) }}"
                                          onsubmit="return confirm('Activate {{ addslashes($c['name']) }} for 1 year?')">
                                        @csrf
                                        <button class="btn btn-sm btn-success btn-action">
                                            <i class="bi bi-check-circle"></i> Activate
                                        </button>
                                    </form>
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
                {{-- Shown when a filter matches nothing --}}
                <tr id="noMatchRow" style="display:none;">
                    <td colspan="9" class="text-center text-muted py-4">No companies with this status.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


{{-- Recent Logins --}}
<div class="card mt-4">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-geo-alt-fill text-primary"></i>
        <span>Recent Logins — IP &amp; Location</span>
        <span class="badge bg-secondary ms-auto">Last 20</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:.875rem;">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>IP Address</th>
                    <th>Country</th>
                    <th>Last Login</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentLogins as $login)
                    <tr>
                        <td class="fw-semibold">{{ $login['name'] }}</td>
                        <td class="text-muted">{{ $login['email'] }}</td>
                        <td><code>{{ $login['ip'] }}</code></td>
                        <td>{{ $login['country'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($login['last_login_at'])->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No logins recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons   = document.querySelectorAll('#statusFilter button');
    const rows      = document.querySelectorAll('.tenant-row');
    const counter   = document.getElementById('companyCount');
    const noMatch   = document.getElementById('noMatchRow');

    // Maps each filter to its solid/outline button colour so we can
    // restore the outline look on the buttons that aren't selected.
    const colors = {
        all:       'dark',
        active:    'success',
        trial:     'warning',
        expired:   'danger',
        suspended: 'secondary',
    };

    function setActive(activeBtn) {
        buttons.forEach(b => {
            const c = colors[b.dataset.filter];
            b.classList.remove('active', 'btn-' + c, 'btn-outline-' + c);
            if (b === activeBtn) {
                b.classList.add('active', 'btn-' + c);
            } else {
                // 'all' has no outline variant in the original, keep it dark-outline
                b.classList.add(b.dataset.filter === 'all' ? 'btn-outline-dark' : 'btn-outline-' + c);
            }
        });
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', function () {
            const filter = this.dataset.filter;
            setActive(this);

            let visible = 0;
            rows.forEach(row => {
                const match = (filter === 'all' || row.dataset.status === filter);
                row.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            counter.textContent = visible + ' shown';
            if (noMatch) noMatch.style.display = (visible === 0) ? '' : 'none';
        });
    });
});
</script>
@endpush