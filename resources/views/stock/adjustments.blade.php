@extends('layouts.app')

@section('title', 'Stock Adjustments & Write-offs')
@section('page-title', 'Stock Adjustments & Write-offs')

@push('styles')
<style>
    :root {
        --danger: #ef4444;
        --danger-light: rgba(239, 68, 68, 0.1);
        --warning: #f59e0b;
        --warning-light: rgba(245, 158, 11, 0.1);
        --success: #10b981;
        --success-light: rgba(16, 185, 129, 0.1);
        --primary: #4f46e5;
        --primary-light: rgba(79, 70, 229, 0.1);
        --surface: #fff;
        --border: #e2e8f0;
        --text-primary: #0f172a;
        --text-muted: #64748b;
        --radius: 16px;
        --radius-sm: 10px;
        --shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);
    }

    .stat-card {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 4px; height: 100%;
        border-radius: 4px 0 0 4px;
    }
    .stat-card.danger::before { background: var(--danger); }
    .stat-card.warning::before { background: var(--warning); }

    .stat-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
    }
    .stat-icon.danger { background: var(--danger-light); color: var(--danger); }
    .stat-icon.warning { background: var(--warning-light); color: var(--warning); }

    .stat-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
    .stat-value { font-size: 1.6rem; font-weight: 800; color: var(--text-primary); line-height: 1; }

    .form-card {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        overflow: hidden;
    }
    .form-card-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border);
        background: #fef2f2;
        display: flex; align-items: center; gap: 0.75rem;
    }
    .form-card-body { padding: 1.5rem; }

    .reason-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.6rem;
    }
    .reason-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 0.75rem 0.5rem;
        border: 2px solid var(--border);
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: all 0.2s ease;
        background: #fff;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text-muted);
        text-align: center;
    }
    .reason-btn i { font-size: 1.3rem; }
    .reason-btn:hover { border-color: var(--danger); color: var(--danger); background: var(--danger-light); }
    .reason-btn input[type="radio"] { display: none; }
    .reason-btn.selected { border-color: var(--danger); color: var(--danger); background: var(--danger-light); }

    .reason-btn[data-reason="theft"] i    { color: #6b21a8; }
    .reason-btn[data-reason="damage"] i   { color: #ea580c; }
    .reason-btn[data-reason="expiry"] i   { color: #0369a1; }
    .reason-btn[data-reason="miscounted"] i { color: #0f766e; }
    .reason-btn[data-reason="lost"] i     { color: #b45309; }
    .reason-btn[data-reason="other"] i    { color: #475569; }

    .logs-card {
        background: var(--surface);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        overflow: hidden;
    }
    .logs-header {
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid var(--border);
        background: #f8fafc;
        display: flex; align-items: center; justify-content: space-between;
    }
    .logs-header h5 { font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin: 0; }
    .table { font-size: 0.83rem; margin: 0; }
    .table th { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted); background: #f8fafc; border-color: var(--border); padding: 0.6rem 1rem; }
    .table td { vertical-align: middle; padding: 0.65rem 1rem; border-color: var(--border); color: var(--text-primary); }
    .table tbody tr:last-child td { border-bottom: none; }

    .badge-reason {
        padding: 0.3em 0.75em;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .reason-theft     { background: rgba(107, 33, 168, 0.1); color: #6b21a8; }
    .reason-damage    { background: rgba(234, 88, 12, 0.1);  color: #ea580c; }
    .reason-expiry    { background: rgba(3, 105, 161, 0.1);  color: #0369a1; }
    .reason-miscounted{ background: rgba(15, 118, 110, 0.1); color: #0f766e; }
    .reason-lost      { background: rgba(180, 83, 9, 0.1);   color: #b45309; }
    .reason-other     { background: rgba(71, 85, 105, 0.1);  color: #475569; }
    .reason-adjustment{ background: var(--warning-light);    color: var(--warning); }
    .reason-damage-type{ background: rgba(234, 88, 12, 0.1); color: #ea580c; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card danger">
                <div class="stat-icon danger"><i class="bi bi-shield-exclamation"></i></div>
                <div>
                    <div class="stat-label">Today's Write-offs</div>
                    <div class="stat-value">{{ $totalWrittenOff }}</div>
                    <div class="stat-label mt-1">units removed</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card warning">
                <div class="stat-icon warning"><i class="bi bi-calendar-x"></i></div>
                <div>
                    <div class="stat-label">This Month</div>
                    <div class="stat-value">{{ $monthWrittenOff }}</div>
                    <div class="stat-label mt-1">units written off</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Write-off Form --}}
        <div class="col-12 col-lg-4">
            <div class="form-card">
                <div class="form-card-header">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                    <div>
                        <div class="fw-bold" style="color: var(--danger);">Record Stock Write-off</div>
                        <div class="small text-muted">Theft, damage, expiry or loss</div>
                    </div>
                </div>
                <div class="form-card-body">
                    @if(session('success'))
                        <div class="alert alert-success border-0 py-2 small mb-3">
                            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger border-0 py-2 small mb-3">
                            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                        </div>
                    @endif

                    <form action="{{ route('stock.adjustments.store') }}" method="POST" id="writeoffForm">
                        @csrf

                        {{-- Reason Selection --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-2">Reason for Write-off</label>
                            <div class="reason-grid" id="reasonGrid">
                                @foreach([
                                    ['reason' => 'theft',      'icon' => 'bi-person-slash',      'label' => 'Theft'],
                                    ['reason' => 'damage',     'icon' => 'bi-hammer',            'label' => 'Damage'],
                                    ['reason' => 'expiry',     'icon' => 'bi-calendar-x',        'label' => 'Expiry'],
                                    ['reason' => 'miscounted', 'icon' => 'bi-calculator',        'label' => 'Miscounted'],
                                    ['reason' => 'lost',       'icon' => 'bi-question-diamond',  'label' => 'Lost'],
                                    ['reason' => 'other',      'icon' => 'bi-three-dots',        'label' => 'Other'],
                                ] as $r)
                                <label class="reason-btn" data-reason="{{ $r['reason'] }}">
                                    <input type="radio" name="reason" value="{{ $r['reason'] }}" required>
                                    <i class="bi {{ $r['icon'] }}"></i>
                                    {{ $r['label'] }}
                                </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Product --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Product</label>
                            <select name="product_id" class="form-select form-select-sm" required>
                                <option value="">— Select product —</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }} ({{ $p->sku }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Branch --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Branch</label>
                            <select name="branch_id" class="form-select form-select-sm" required
                                {{ auth()->user()->branch_id ? 'disabled' : '' }}>
                                @if(auth()->user()->branch_id)
                                    <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                                @endif
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}"
                                        {{ (old('branch_id', auth()->user()->branch_id) == $b->id) ? 'selected' : '' }}>
                                        {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(auth()->user()->branch_id)
                                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                            @endif
                        </div>

                        {{-- Quantity --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Quantity to Write Off</label>
                            <input type="number" name="quantity" class="form-control form-control-sm"
                                min="1" placeholder="Enter number of units" required value="{{ old('quantity') }}">
                        </div>

                        {{-- Notes --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold small">Notes <span class="text-muted fw-normal">(optional)</span></label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2"
                                placeholder="Describe what happened...">{{ old('notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 rounded-pill py-2 fw-bold">
                            <i class="bi bi-exclamation-triangle me-2"></i>Record Write-off
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Logs --}}
        <div class="col-12 col-lg-8">
            <div class="logs-card">
                <div class="logs-header">
                    <h5><i class="bi bi-clock-history me-2 text-danger"></i>Write-off History</h5>
                    <span class="badge bg-danger rounded-pill">{{ $logs->total() }} records</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date / Time</th>
                                <th>Product</th>
                                <th>Branch</th>
                                <th>Qty</th>
                                <th>Reason</th>
                                <th>Recorded By</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            @php
                                // Parse reason from notes prefix like "[THEFT] ..."
                                preg_match('/^\[([A-Z]+)\]/', $log->notes ?? '', $match);
                                $reason = strtolower($match[1] ?? $log->type);
                                $display = ucfirst($reason);
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $log->created_at->format('M d, Y') }}</div>
                                    <div class="text-muted" style="font-size:0.75rem;">{{ $log->created_at->format('H:i') }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $log->product->name ?? '—' }}</div>
                                    <div class="text-muted" style="font-size:0.75rem;">{{ $log->product->sku ?? '' }}</div>
                                </td>
                                <td>{{ $log->branch->name ?? '—' }}</td>
                                <td>
                                    <span class="fw-bold text-danger">{{ abs($log->quantity) }}</span>
                                </td>
                                <td>
                                    <span class="badge-reason reason-{{ $reason }}">{{ $display }}</span>
                                </td>
                                <td>{{ $log->user->name ?? '—' }}</td>
                                <td>
                                    <small class="text-muted">
                                        {{ $log->notes ? preg_replace('/^\[[A-Z]+\]\s*/', '', $log->notes) : '—' }}
                                    </small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-shield-check fs-2 d-block mb-2 opacity-25"></i>
                                    No write-offs recorded yet
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($logs->hasPages())
                <div class="p-3 border-top">
                    {{ $logs->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Reason button selection
    const reasonBtns = document.querySelectorAll('.reason-btn');
    reasonBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            reasonBtns.forEach(b => b.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });
});
</script>
@endpush
