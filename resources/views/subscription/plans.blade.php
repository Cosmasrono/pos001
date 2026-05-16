@extends('layouts.app')

@section('title', 'Subscribe')
@section('page-title', 'Subscribe to WingPOS')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            {{-- Status Banner --}}
            @if($company->subscription_status === 'trial')
                <div class="alert alert-warning mb-4">
                    <i class="bi bi-clock me-2"></i>
                    Your free trial ends in <strong>{{ $company->trialDaysRemaining() }} day(s)</strong>.
                    Subscribe now to keep access.
                </div>
            @elseif(in_array($company->subscription_status, ['expired', 'suspended']))
                <div class="alert alert-danger mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Your subscription has expired. Choose a plan below to reactivate.
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger mb-4">
                    <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
                </div>
            @endif

            <h5 class="fw-bold mb-1">Choose a Plan</h5>
            <p class="text-muted mb-4">Pay via M-Pesa. Your phone will receive a prompt to confirm.</p>

            {{-- Plan Cards --}}
            <div class="row g-4 mb-4" id="planCards">
                @foreach($plans as $key => $plan)
                <div class="col-md-6">
                    <div class="card plan-card h-100 {{ $key === 'annual' ? 'border-primary' : '' }}"
                         data-plan="{{ $key }}"
                         data-amount="{{ $plan['amount'] }}"
                         style="cursor:pointer; transition: all 0.2s;">
                        @if($key === 'annual')
                            <div class="badge bg-primary position-absolute top-0 end-0 m-3">Best Value</div>
                        @endif
                        <div class="card-body p-4 text-center">
                            <h5 class="fw-bold mb-1">{{ $plan['label'] }}</h5>
                            <div class="my-3">
                                <span class="fs-1 fw-bold text-primary">
                                    KES {{ number_format($plan['amount']) }}
                                </span>
                                <span class="text-muted"> / {{ $plan['duration'] }}</span>
                            </div>
                            <p class="text-muted small mb-3">{{ $plan['description'] }}</p>
                            <div class="plan-check" style="display:none;">
                                <i class="bi bi-check-circle-fill text-success fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Payment Form --}}
            <div class="card" id="paymentForm" style="display:none;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-phone me-2 text-success"></i>Enter M-Pesa Number
                    </h6>
                    <form action="{{ route('subscribe.initiate') }}" method="POST">
                        @csrf
                        <input type="hidden" name="plan" id="selectedPlan" value="">

                        <div class="mb-3">
                            <label class="form-label">M-Pesa Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-phone"></i>
                                </span>
                                <input type="tel"
                                       name="phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       placeholder="e.g. 0712345678"
                                       value="{{ old('phone', auth()->user()->company->phone ?? '') }}"
                                       required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">The number that will receive the M-Pesa STK push prompt.</div>
                        </div>

                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded mb-3" id="selectedSummary">
                            <i class="bi bi-tag-fill text-primary fs-5"></i>
                            <div>
                                <div class="fw-semibold" id="summaryLabel">—</div>
                                <div class="text-muted small" id="summaryAmount">—</div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-3 fs-6">
                            <i class="bi bi-phone me-2"></i>Send M-Pesa Prompt
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const plans = @json($plans);

    document.querySelectorAll('.plan-card').forEach(card => {
        card.addEventListener('click', function () {
            // Deselect all
            document.querySelectorAll('.plan-card').forEach(c => {
                c.classList.remove('border-success', 'shadow-lg');
                c.querySelector('.plan-check').style.display = 'none';
            });

            // Select clicked
            this.classList.add('border-success', 'shadow-lg');
            this.querySelector('.plan-check').style.display = 'block';

            const key    = this.dataset.plan;
            const amount = this.dataset.amount;
            const plan   = plans[key];

            document.getElementById('selectedPlan').value = key;
            document.getElementById('summaryLabel').textContent  = plan.label + ' Plan';
            document.getElementById('summaryAmount').textContent = 'KES ' + parseInt(amount).toLocaleString() + ' / ' + plan.duration;
            document.getElementById('paymentForm').style.display = 'block';
            document.getElementById('paymentForm').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });
</script>
@endpush
