@extends('layouts.app')

@section('title', 'Shift Reconciliation Report')
@section('page-title', 'Reconciliation Report')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-xl-8 mx-auto">
            {{-- Header Card --}}
            <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="border-radius: 20px;">
                <div class="card-body p-0">
                    <div class="p-4 @if($shift->cash_shortage_overage < 0) bg-danger @elseif($shift->cash_shortage_overage > 0) bg-warning @else bg-success @endif text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase opacity-75 fw-bold mb-1">Shift Status: {{ strtoupper($shift->status) }}</h6>
                                <h2 class="mb-0 fw-bold">
                                    @if($shift->cash_shortage_overage == 0)
                                        ✅ Shift Balanced
                                    @elseif($shift->cash_shortage_overage < 0)
                                        ⚠️ Cash Shortage
                                    @else
                                        💰 Cash Overage
                                    @endif
                                </h2>
                            </div>
                            <div class="text-end">
                                <h1 class="mb-0 fw-bold">KES {{ number_format(abs($shift->cash_shortage_overage), 2) }}</h1>
                                <small class="opacity-75">Variance Amount</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4 bg-white">
                        <div class="row g-4">
                            <div class="col-md-3 border-end">
                                <small class="text-muted d-block text-uppercase fw-bold mb-1">Cashier</small>
                                <h6 class="mb-0 fw-bold">{{ $shift->cashier->name }}</h6>
                            </div>
                            <div class="col-md-3 border-end">
                                <small class="text-muted d-block text-uppercase fw-bold mb-1">Branch</small>
                                <h6 class="mb-0 fw-bold">{{ $shift->branch->name }}</h6>
                            </div>
                            <div class="col-md-3 border-end">
                                <small class="text-muted d-block text-uppercase fw-bold mb-1">Started</small>
                                <h6 class="mb-0 fw-bold">{{ $shift->opened_at->format('M d, h:i A') }}</h6>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block text-uppercase fw-bold mb-1">Closed</small>
                                <h6 class="mb-0 fw-bold">{{ $shift->closed_at->format('M d, h:i A') }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                {{-- Financial Summary --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-calculator me-2"></i>Sales Summary</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Cash Sales</span>
                                <span class="fw-bold">KES {{ number_format($shift->total_cash_sales, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">M-Pesa Sales</span>
                                <span class="fw-bold">KES {{ number_format($shift->total_mpesa_sales, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Card Sales</span>
                                <span class="fw-bold">KES {{ number_format($shift->total_card_sales, 2) }}</span>
                            </div>
                            <hr class="opacity-10">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-bold mb-0">Total Gross Sales</h6>
                                <h6 class="fw-bold mb-0 text-primary">KES {{ number_format($shift->total_cash_sales + $shift->total_mpesa_sales + $shift->total_card_sales, 2) }}</h6>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cash Reconciliation --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="mb-0 fw-bold text-success"><i class="bi bi-safe me-2"></i>Cash Reconciliation</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Opening Cash</span>
                                <span class="fw-bold">KES {{ number_format($shift->opening_cash, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Expected Cash Sales</span>
                                <span class="fw-bold">+ KES {{ number_format($shift->total_cash_sales, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Refunds/Payouts</span>
                                <span class="fw-bold text-danger">- KES {{ number_format($shift->total_refunds, 2) }}</span>
                            </div>
                            <hr class="opacity-10">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold">System Expected Cash</span>
                                <span class="fw-bold">KES {{ number_format($shift->expected_closing_cash, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between p-3 rounded-3 @if($shift->cash_shortage_overage < 0) bg-danger-light @else bg-success-light @endif">
                                <span class="fw-bold @if($shift->cash_shortage_overage < 0) text-danger @else text-success @endif">Physical Cash Counted</span>
                                <span class="fw-bold @if($shift->cash_shortage_overage < 0) text-danger @else text-success @endif">KES {{ number_format($shift->closing_cash_counted, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold small text-muted text-uppercase mb-2">Opening Notes</h6>
                            <p class="mb-0">{{ $shift->opening_notes ?: 'No opening notes provided.' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold small text-muted text-uppercase mb-2">Closing Notes</h6>
                            <p class="mb-0">{{ $shift->closing_notes ?: 'No closing notes provided.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-3 pb-5">
                <button onclick="window.print()" class="btn btn-primary px-4 rounded-pill">
                    <i class="bi bi-printer me-2"></i> Print Report
                </button>
                <a href="{{ route('sales.pos') }}" class="btn btn-outline-primary px-4 rounded-pill">
                    <i class="bi bi-shop me-2"></i> Back to POS
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .sidebar, .navbar-top, .btn, .nav-section-label { display: none !important; }
        .main-content { padding: 0 !important; margin: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #eee !important; }
        .bg-danger, .bg-success, .bg-warning { -webkit-print-color-adjust: exact; }
    }
</style>
@endsection
