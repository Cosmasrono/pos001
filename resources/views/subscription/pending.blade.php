@extends('layouts.app')

@section('title', 'Awaiting Payment')
@section('page-title', 'M-Pesa Payment')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card text-center p-4">
                <div class="card-body">

                    {{-- Spinner / Success / Error icons --}}
                    <div id="iconPending">
                        <div class="spinner-border text-success mb-3" style="width:3.5rem;height:3.5rem;" role="status"></div>
                    </div>
                    <div id="iconSuccess" style="display:none;">
                        <i class="bi bi-check-circle-fill text-success mb-3" style="font-size:3.5rem;"></i>
                    </div>
                    <div id="iconFailed" style="display:none;">
                        <i class="bi bi-x-circle-fill text-danger mb-3" style="font-size:3.5rem;"></i>
                    </div>

                    <h4 id="statusTitle" class="fw-bold mt-2">Waiting for M-Pesa…</h4>
                    <p id="statusMessage" class="text-muted">
                        A prompt has been sent to <strong>{{ $payment->phone }}</strong>.<br>
                        Enter your M-Pesa PIN to complete the payment.
                    </p>

                    {{-- Payment summary --}}
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded mb-4">
                        <div class="text-start">
                            <div class="fw-semibold">{{ ucfirst($payment->plan) }} Plan</div>
                            <div class="text-muted small">WingPOS Subscription</div>
                        </div>
                        <div class="fw-bold text-primary fs-5">
                            KES {{ number_format($payment->amount) }}
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div id="actionsDefault">
                        <p class="text-muted small mb-3">
                            Didn't get the prompt?
                            <a href="{{ route('subscribe.plans') }}">Go back and try again</a>.
                        </p>
                    </div>
                    <div id="actionsDone" style="display:none;">
                        <a href="{{ route('dashboard') }}" class="btn btn-success w-100">
                            <i class="bi bi-house me-2"></i>Go to Dashboard
                        </a>
                    </div>
                    <div id="actionsRetry" style="display:none;">
                        <a href="{{ route('subscribe.plans') }}" class="btn btn-primary w-100">
                            <i class="bi bi-arrow-clockwise me-2"></i>Try Again
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const statusUrl = "{{ route('subscribe.status', $payment->id) }}";
    let attempts = 0;
    const maxAttempts = 40; // ~2 minutes of polling

    function poll() {
        if (attempts >= maxAttempts) {
            showFailed('Payment timed out. Please try again.');
            return;
        }
        attempts++;

        fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'completed') {
                    showSuccess();
                } else if (data.status === 'failed') {
                    showFailed(data.reason || 'Payment was not completed.');
                } else {
                    setTimeout(poll, 3000);
                }
            })
            .catch(() => setTimeout(poll, 5000));
    }

    function showSuccess() {
        document.getElementById('iconPending').style.display  = 'none';
        document.getElementById('iconSuccess').style.display  = 'block';
        document.getElementById('statusTitle').textContent    = 'Payment Confirmed!';
        document.getElementById('statusMessage').innerHTML    = 'Your <strong>{{ ucfirst($payment->plan) }}</strong> subscription is now active. Welcome!';
        document.getElementById('actionsDefault').style.display = 'none';
        document.getElementById('actionsDone').style.display    = 'flex';
        document.getElementById('actionsDone').style.display    = 'block';
    }

    function showFailed(reason) {
        document.getElementById('iconPending').style.display  = 'none';
        document.getElementById('iconFailed').style.display   = 'block';
        document.getElementById('statusTitle').textContent    = 'Payment Failed';
        document.getElementById('statusMessage').textContent  = reason;
        document.getElementById('actionsDefault').style.display = 'none';
        document.getElementById('actionsRetry').style.display   = 'block';
    }

    setTimeout(poll, 4000);
</script>
@endpush
