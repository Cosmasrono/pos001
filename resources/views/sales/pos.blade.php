@extends('layouts.app')

@section('title', 'Wing POS')

@section('page-title')
    <span class="d-md-none">POS</span>
    <span class="d-none d-md-inline">Point of Sale (POS)</span>
@endsection

@section('navbar-actions')
    <div class="d-flex align-items-center gap-1 gap-md-2">
        @if($hasActiveShift)
            <div class="badge bg-success-light text-success border border-success d-none d-md-block">
                <i class="bi bi-clock-history"></i> Shift Started: {{ $shift->opened_at->format('h:i A') }}
            </div>
            <button type="button" class="btn btn-sm btn-danger rounded-pill px-2 px-md-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#closeShiftModal">
                <i class="bi bi-power"></i> <span class="d-none d-md-inline ms-1">Close Shift</span>
            </button>
        @else
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-2 px-md-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#openShiftModal">
                <i class="bi bi-play-fill"></i> <span class="d-none d-md-inline ms-1">Open Shift</span>
            </button>
        @endif
    </div>
@endsection

@section('content')
<style>
    body {
        background: radial-gradient(circle at 10% 20%, rgba(79, 70, 229, 0.05) 0%, rgba(248, 250, 252, 1) 90.2%);
        min-height: 100vh;
    }
    
    .pos-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    }

    .search-input-wrapper {
        position: relative;
    }

    .search-input-wrapper i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }

    #productSearch {
        padding-left: 45px;
        height: 50px;
        border-radius: 12px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        background: white;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    #productSearch:focus {
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        border-color: var(--primary);
    }

    .product-item {
        border: none;
        border-radius: 12px;
        margin-bottom: 8px;
        background: white;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .product-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
        border-color: rgba(79, 70, 229, 0.2);
    }

    .qty-selector {
        background: #f1f5f9;
        border: none;
        text-align: center;
        width: 60px;
    }

    .cart-table th {
        background: #f8fafc;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        border: none;
    }

    .checkout-summary {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
    }

    /* ── Responsive POS layout ── */
    @media (min-width: 768px) {
        .pos-row {
            height: calc(100vh - 130px);
            overflow: hidden;
        }

        .pos-left-col {
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }

        /* Search card — fixed height with scrollable results */
        .pos-search-card {
            flex: 0 0 auto;
            max-height: 45%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            margin-bottom: 0.75rem;
        }

        .pos-search-card .card-body {
            flex: 1 1 auto;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding-bottom: 0;
        }

        #searchResults {
            flex: 1 1 auto;
            overflow-y: auto;
            margin-top: 0.5rem;
            padding-right: 4px;
        }

        /* Cart card — fills remaining space with scrollable body */
        .pos-cart-card {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 0;
        }

        .pos-cart-card .card-body {
            flex: 1 1 auto;
            overflow-y: auto;
            padding-top: 0.5rem;
        }

        /* Right checkout column — scrollable independently */
        .pos-right-col {
            height: 100%;
            overflow-y: auto;
        }
    }

    @media (max-width: 767px) {
        .pos-row {
            height: auto;
            overflow: visible;
        }
        .pos-search-card {
            margin-bottom: 1rem;
        }
        .pos-cart-card {
            margin-bottom: 1rem;
            max-height: 400px;
        }
        .pos-cart-card .card-body {
            overflow-y: auto;
        }
    }

    /* Floating Checkout Button for Mobile */
    #mobileCheckoutBtn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1050;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        border: none;
        box-shadow: 0 4px 20px rgba(79, 70, 229, 0.4);
        display: none; /* Hidden by default, shown by JS on mobile */
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #mobileCheckoutBtn:hover {
        transform: scale(1.1);
        background: var(--primary-hover);
    }

    #mobileCheckoutBtn .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: var(--danger);
        color: white;
        border-radius: 50%;
        padding: 5px 8px;
        font-size: 0.75rem;
        border: 2px solid white;
    }
</style>

<button type="button" id="mobileCheckoutBtn" class="d-md-none" title="Go to Checkout">
    <i class="bi bi-cart-check-fill"></i>
    <span class="badge" id="mobileCartCount">0</span>
</button>

<div class="container-fluid">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="row pos-row">
        <!-- Left Panel - Product Search & Cart -->
        <div class="col-md-8 pos-left-col">
            <!-- Product Search -->
            <div class="card pos-search-card">
                <div class="card-header">
                    <h5 class="mb-0">Search Products</h5>
                </div>
                <div class="card-body">
                    <div class="search-input-wrapper">
                        <i class="bi bi-search"></i>
                        <input type="text" id="productSearch" class="form-control" placeholder="Search by product name, code, or barcode...">
                    </div>
                    <div id="searchResults" class="mt-2">
                        <div class="text-center">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sale Items Cart -->
            <div class="card pos-cart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Sale Items</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-danger" id="clearCart">Clear</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="cart-table">
                                <tr>
                                    <th>Product</th>
                                    <th width="100" class="text-center">Qty</th>
                                    <th width="120" class="text-end">Unit Price</th>
                                    <th width="120" class="text-end">Total</th>
                                    <th width="80" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="cartItems">
                            </tbody>
                            <tfoot>
                                <tr id="emptyCart">
                                    <td colspan="5" class="text-center text-muted">No items added yet</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Checkout -->
        <div class="col-md-4 pos-right-col">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Checkout</h5>
                </div>
                <div class="card-body">
                    <form id="posForm" method="POST" action="{{ route('sales.store') }}">
                        @csrf

                        <!-- Totals Section -->
                        <div class="checkout-summary mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <strong id="subtotal" class="text-dark">KES 0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Discount</span>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <span class="input-group-text bg-transparent border-0 pe-0">KES</span>
                                    <input type="number" name="discount" id="discount" class="form-control form-control-sm border-0 bg-transparent text-end fw-bold" value="0" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2" id="tradeInDisplay" style="display: none !important;">
                                <span class="text-success fw-bold">Trade-in Credit</span>
                                <strong id="tradeInAmountText" class="text-success">- KES 0.00</strong>
                            </div>
                            <hr class="opacity-10">
                            <div class="d-flex justify-content-between align-items-center mb-0">
                                <h5 class="mb-0 fw-bold">Total</h5>
                                <h4 class="text-primary mb-0 fw-bold" id="totalAmount">KES 0.00</h4>
                            </div>
                        </div>

                        <!-- Trade-in Section -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-success w-100 rounded-3 mb-2" data-bs-toggle="modal" data-bs-target="#tradeInModal">
                                <i class="bi bi-arrow-repeat"></i> Add Trade-in
                            </button>
                            <div id="tradeInList" class="small"></div>
                        </div>

                        <!-- Promotion Selection -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">🎁 PROMOTION</label>
                            <select name="promotion_id" id="promotion" class="form-select border-0 bg-light rounded-3">
                                <option value="" data-type="fixed" data-value="0">No Promotion</option>
                                @foreach($promotions ?? [] as $promotion)
                                    <option value="{{ $promotion->id }}" 
                                            data-type="{{ $promotion->type }}" 
                                            data-value="{{ $promotion->value }}"
                                            data-min="{{ $promotion->min_spend }}">
                                        {{ $promotion->name }} 
                                        ({{ $promotion->type === 'percentage' ? $promotion->value.'%' : 'KES '.$promotion->value }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Customer Selection -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">👤 CUSTOMER</label>
                            <select name="customer_id" id="customer" class="form-select border-0 bg-light rounded-3">
                                <option value="">Walk-in Customer</option>
                                @foreach($customers ?? [] as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">PAYMENT METHOD</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="payment_method" id="pay_cash" value="cash" checked autocomplete="off">
                                    <label class="btn btn-outline-light text-dark w-100 border bg-white" for="pay_cash">💵 Cash</label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="payment_method" id="pay_mpesa" value="mpesa" autocomplete="off">
                                    <label class="btn btn-outline-light text-dark w-100 border bg-white" for="pay_mpesa">📱 M-Pesa</label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="payment_method" id="pay_card" value="card" autocomplete="off">
                                    <label class="btn btn-outline-light text-dark w-100 border bg-white" for="pay_card">💳 Card</label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="payment_method" id="pay_credit" value="credit" autocomplete="off">
                                    <label class="btn btn-outline-light text-dark w-100 border bg-white" for="pay_credit">📋 Credit</label>
                                </div>
                            </div>
                            <input type="hidden" name="payment_method" id="paymentMethod" value="cash">
                        </div>

                        <!-- M-Pesa Phone Number (Hidden by default) -->
                        <div class="mb-3" id="mpesaPhoneField" style="display: none;">
                            <label class="form-label">M-Pesa Phone Number</label>
                            <input type="tel" name="mpesa_phone" id="mpesaPhone" class="form-control" placeholder="07XXXXXXXX or 2547XXXXXXXX">
                            <small class="text-muted">Enter the phone number to receive STK Push</small>
                        </div>

                        <!-- Amount Tendered -->
                        <div class="mb-3" id="amountTenderedField">
                            <label class="form-label">Amount Tendered</label>
                            <input type="number" name="amount_tendered" id="amountTendered" class="form-control" step="0.01" min="0">
                        </div>

                        <!-- Change Due -->
                        <div class="mb-4" id="changeDueField">
                            <div class="d-flex justify-content-between">
                                <strong>Change Due</strong>
                                <strong class="text-success" id="changeDue">KES 0.00</strong>
                            </div>
                        </div>

                        <!-- Hidden Cart Data -->
                        <input type="hidden" name="cart_data" id="cartData">
                        <input type="hidden" name="trade_in_data" id="tradeInData">
                        <input type="hidden" name="trade_in_amount" id="tradeInAmountInput">
                        <input type="hidden" name="subtotal_amount" id="subtotalInput">
                        <input type="hidden" name="tax_amount" id="taxInput">
                        <input type="hidden" name="total_amount" id="totalInput">

                        <!-- Action Buttons -->
                        @if(!($hasActiveShift ?? true))
                            <div class="alert alert-warning">
                                ⚠️ Open a shift to complete sales
                            </div>
                        @endif

                        <div class="d-grid gap-2">
                            @if($hasActiveShift)
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm py-3" id="completeSaleBtn">
                                    <i class="bi bi-check-circle me-2"></i>Complete Sale
                                </button>
                            @else
                                <button type="button" class="btn btn-warning btn-lg rounded-pill shadow-sm py-3" data-bs-toggle="modal" data-bs-target="#openShiftModal">
                                    <i class="bi bi-play-circle me-2"></i>Open Shift to Sell
                                </button>
                            @endif
                            <button type="button" class="btn btn-link text-muted btn-sm" id="cancelBtn">Reset Cart</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Trade-in Modal -->
<div class="modal fade" id="tradeInModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">🔄 Device Trade-in</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="tradeInForm">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">DEVICE MODEL</label>
                        <input type="text" id="tradeInModel" class="form-control" placeholder="e.g. iPhone 13 Pro" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">IMEI / SERIAL NUMBER</label>
                        <input type="text" id="tradeInImei" class="form-control" placeholder="Enter device identifier">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">CONDITION</label>
                            <select id="tradeInCondition" class="form-select">
                                <option value="Excellent">Excellent</option>
                                <option value="Good" selected>Good</option>
                                <option value="Fair">Fair</option>
                                <option value="Broken">Broken</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">TRADE-IN VALUE (KES)</label>
                            <input type="number" id="tradeInValue" class="form-control fw-bold text-success" step="0.01" min="0" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="addTradeInBtn">Add to Sale</button>
            </div>
        </div>
    </div>
</div>

<!-- M-Pesa Payment Modal -->
<div class="modal fade" id="mpesaModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">📱 M-Pesa Payment</h5>
            </div>
            <div class="modal-body text-center">
                <div id="mpesaStatusPending" style="display: none;">
                    <div class="spinner-border text-success mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="mb-3">Waiting for Payment...</h5>
                    <p class="text-muted">
                        1. Check your phone for M-Pesa prompt<br>
                        2. Enter your M-Pesa PIN<br>
                        3. Confirm the payment
                    </p>
                    <p class="fw-bold" id="mpesaPhoneDisplay"></p>
                    <p class="fw-bold text-success" id="mpesaAmountDisplay"></p>
                </div>
                <div id="mpesaStatusSuccess" style="display: none;">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-success">Payment Successful!</h5>
                    <p id="mpesaReceiptNumber"></p>
                </div>
                <div id="mpesaStatusFailed" style="display: none;">
                    <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-danger">Payment Failed</h5>
                    <p id="mpesaErrorMessage" class="text-muted"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="mpesaCancelBtn">Cancel</button>
                <button type="button" class="btn btn-success" id="mpesaFinishBtn" style="display: none;">Complete Sale</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/dexie/dist/dexie.js"></script>
<!-- Open Shift Modal -->
<div class="modal fade" id="openShiftModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <form action="{{ route('shifts.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold">🚀 Open New Shift</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Opening Cash Balance (KES)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light border-end-0 text-muted small">KES</span>
                            <input type="number" name="opening_cash" class="form-control border-start-0 fw-bold" placeholder="0.00" step="0.01" min="0" required>
                        </div>
                        <small class="text-muted mt-2 d-block">Enter the physical cash currently in your drawer.</small>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase">Notes</label>
                        <textarea name="opening_notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold">
                        <i class="bi bi-play-fill"></i> Start Shift
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Close Shift (Reconciliation) Modal -->
<div class="modal fade" id="closeShiftModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <form action="{{ route('shifts.close') }}" method="POST">
                @csrf
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold">🏁 Close Shift & Reconcile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 mb-4" style="background: var(--info-light);">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-info-circle-fill fs-4 text-info"></i>
                            <div class="small">
                                Counting your money accurately ensures your sales match your drawer.
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-primary">Physical Cash Counted (KES)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-primary text-white border-0">KES</span>
                            <input type="number" name="closing_cash" class="form-control border-primary fw-bold" placeholder="0.00" step="0.01" min="0" required>
                        </div>
                        <small class="text-muted mt-2 d-block">Enter the total cash physically present in the drawer.</small>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">M-PESA TOTAL</label>
                            <input type="number" name="closing_mpesa" class="form-control form-control-sm bg-light border-0 fw-bold" value="0.00" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">CARD TOTAL</label>
                            <input type="number" name="closing_card" class="form-control form-control-sm bg-light border-0 fw-bold" value="0.00" readonly>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase">Closing Notes</label>
                        <textarea name="closing_notes" class="form-control" rows="2" placeholder="Explain any shortages or issues..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="submit" class="btn btn-danger w-100 rounded-pill py-3 fw-bold shadow-lg" onclick="return confirm('Are you sure you want to close this shift and submit reconciliation?')">
                        <i class="bi bi-power"></i> Submit & Close Shift
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialize Dexie DB
    const db = new Dexie("WingPOS_DB");
    db.version(1).stores({
        products: 'id, name, sku, barcode, price',
        sales_queue: '++id, customer_id, cart_data, payment_method, total_amount, status'
    });

    let cart = [];
    let tradeIns = [];
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Trade-in Logic
    const addTradeInBtn = document.getElementById('addTradeInBtn');
    const tradeInList = document.getElementById('tradeInList');
    const tradeInDisplay = document.getElementById('tradeInDisplay');
    const tradeInAmountText = document.getElementById('tradeInAmountText');

    addTradeInBtn.addEventListener('click', function() {
        const model = document.getElementById('tradeInModel').value.trim();
        const imei = document.getElementById('tradeInImei').value.trim();
        const condition = document.getElementById('tradeInCondition').value;
        const value = parseFloat(document.getElementById('tradeInValue').value) || 0;

        if (!model || value <= 0) {
            alert('Please enter model and a valid value.');
            return;
        }

        const tradeIn = {
            id: Date.now(),
            model_name: model,
            imei_serial: imei,
            condition: condition,
            value: value
        };

        tradeIns.push(tradeIn);
        updateTradeInUI();
        
        // Reset and close modal
        document.getElementById('tradeInForm').reset();
        bootstrap.Modal.getInstance(document.getElementById('tradeInModal')).hide();
    });

    function updateTradeInUI() {
        if (tradeIns.length === 0) {
            tradeInList.innerHTML = '';
            tradeInDisplay.setAttribute('style', 'display: none !important');
            updateTotals();
            return;
        }

        tradeInDisplay.setAttribute('style', 'display: flex !important');
        
        let html = '<div class="list-group mb-2">';
        tradeIns.forEach((item, index) => {
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center p-2 border-dashed bg-light">
                    <div>
                        <div class="fw-bold">${item.model_name}</div>
                        <div class="x-small text-muted">${item.imei_serial || 'No IMEI'} | ${item.condition}</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-success fw-bold">KES ${item.value.toLocaleString()}</span>
                        <button type="button" class="btn btn-link text-danger p-0" onclick="removeTradeIn(${index})">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        tradeInList.innerHTML = html;
        
        updateTotals();
    }

    window.removeTradeIn = function(index) {
        tradeIns.splice(index, 1);
        updateTradeInUI();
    };

    // API Headers Helper
    const apiHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': CSRF_TOKEN
    };

    // Database Sync Functions
    async function loadCartFromDB() {
        try {
            const response = await fetch('/api/cart');
            if (response.ok) {
                cart = await response.json();
                updateCartUI();
            }
        } catch (e) {
            console.error('Error loading cart:', e);
        }
    }

    async function syncAddToCart(productId, quantity) {
        try {
            const response = await fetch('/api/cart', {
                method: 'POST',
                headers: apiHeaders,
                body: JSON.stringify({ product_id: productId, quantity: quantity })
            });
            if (response.ok) {
                await loadCartFromDB();
            }
        } catch (e) {
            console.error('Error adding to cart:', e);
        }
    }

    async function syncUpdateQuantity(productId, quantity) {
        try {
            const response = await fetch(`/api/cart/${productId}`, {
                method: 'PUT',
                headers: apiHeaders,
                body: JSON.stringify({ quantity: quantity })
            });
            if (response.ok) {
                await loadCartFromDB();
            }
        } catch (e) {
            console.error('Error updating quantity:', e);
        }
    }

    async function syncRemoveItem(productId) {
        try {
            const response = await fetch(`/api/cart/${productId}`, {
                method: 'DELETE',
                headers: apiHeaders
            });
            if (response.ok) {
                await loadCartFromDB();
            }
        } catch (e) {
            console.error('Error removing item:', e);
        }
    }

    async function syncClearCart() {
        try {
            const response = await fetch('/api/cart', {
                method: 'DELETE',
                headers: apiHeaders
            });
            if (response.ok) {
                cart = [];
                tradeIns = [];
                updateCartUI();
                updateTradeInUI();
            }
        } catch (e) {
            console.error('Error clearing cart:', e);
        }
    }

    // Product Search Functionality
    const productSearch = document.getElementById('productSearch');
    const searchResults = document.getElementById('searchResults');

    // Load state from DB and then products
    loadCartFromDB();
    
    // 2. Sync Products to Local DB
    async function syncProductsWithLocal() {
        if (!navigator.onLine) return;
        
        try {
            const response = await fetch('{{ route("pos.products") }}');
            if (!response.ok) {
                const errorData = await response.json();
                console.error('Sync failed:', errorData.error);
                searchResults.innerHTML = `<div class="alert alert-danger">${errorData.error || 'Failed to sync products.'}</div>`;
                return;
            }
            const products = await response.json();
            await db.products.clear();
            await db.products.bulkAdd(products);
            console.log('Local product cache updated');
        } catch (e) {
            console.error('Failed to sync products:', e);
            searchResults.innerHTML = '<div class="alert alert-danger">Error connecting to server. Please check your connection.</div>';
        }
    }

    syncProductsWithLocal();
    loadAllProducts();

    async function loadAllProducts() {
        try {
            const products = await db.products.toArray();
            if (products.length > 0) {
                searchResults.innerHTML = '<div class="alert alert-info py-2 small">Using offline cache. ' + products.length + ' products available.</div>';
                displaySearchResults(products);
            } else {
                searchResults.innerHTML = '<div class="alert alert-warning">No products in local cache. Please go online to sync.</div>';
            }
        } catch (e) {
            console.error('Dexie load error:', e);
            searchResults.innerHTML = '<div class="alert alert-danger">Error loading local products.</div>';
        }
    }

    productSearch.addEventListener('input', async function() {
        const query = this.value.trim().toLowerCase();
        
        if (query.length < 1) {
            loadAllProducts();
            return;
        }

        // Search in local IndexedDB
        const results = await db.products
            .filter(p => 
                p.name.toLowerCase().includes(query) || 
                (p.sku && p.sku.toLowerCase().includes(query)) ||
                (p.barcode && p.barcode.toLowerCase().includes(query))
            )
            .toArray();
            
        displaySearchResults(results);
    });

    function displaySearchResults(products) {
        if (products.length === 0) {
            searchResults.innerHTML = '<div class="alert alert-info">No products found</div>';
            return;
        }

        let html = '<div class="list-group list-group-flush rounded-3 overflow-hidden border-0 shadow-sm">';
        products.forEach((product, idx) => {
            html += `
                <div class="list-group-item product-item p-3 border-0 mb-1" data-id="${product.id}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="product-icon bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-box-seam text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">${product.name}</h6>
                                <small class="text-muted">Code: ${product.code || 'N/A'} | Stock: ${product.stock || 0}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-end">
                                <div class="fw-bold text-primary">KES ${parseFloat(product.price).toLocaleString()}</div>
                                <small class="text-muted">Unit Price</small>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="number" class="form-control form-control-sm qty-selector rounded-pill" 
                                       data-index="${idx}" value="1" min="1" max="${product.stock || 999}" 
                                       style="width: 70px;">
                                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 add-to-cart-btn" 
                                        data-id="${product.id}" data-name="${product.name}" 
                                        data-price="${product.price}">
                                    <i class="bi bi-plus-lg"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        searchResults.innerHTML = html;

        // Add click handlers to add-to-cart buttons
        document.querySelectorAll('.add-to-cart-btn').forEach((btn, idx) => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const container = this.closest('.product-item');
                const qtyInput = container.querySelector('.qty-selector');
                const quantity = parseInt(qtyInput.value) || 1;
                
                syncAddToCart(this.dataset.id, quantity);
                
                // Reset quantity but keep search results
                qtyInput.value = '1';
                // productSearch.value = ''; // Don't clear search
                // loadAllProducts(); // Don't reload everything
            });
        });

        // Add Enter key support to qty-selectors
        document.querySelectorAll('.qty-selector').forEach(input => {
            input.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    const container = this.closest('.product-item');
                    const btn = container.querySelector('.add-to-cart-btn');
                    btn.click();
                }
            });
        });
    }

    // Update Cart UI Display
    function updateCartUI() {
        const cartItems = document.getElementById('cartItems');
        const emptyCart = document.getElementById('emptyCart');
        const mobileCheckoutBtn = document.getElementById('mobileCheckoutBtn');
        const mobileCartCount = document.getElementById('mobileCartCount');
        
        if (cart.length === 0) {
            cartItems.innerHTML = '';
            emptyCart.style.display = 'table-row';
            if (mobileCheckoutBtn) mobileCheckoutBtn.style.display = 'none';
            updateTotals();
            return;
        }
        
        // Show button on mobile if items exist
        if (mobileCheckoutBtn && window.innerWidth < 768) {
            mobileCheckoutBtn.style.display = 'flex';
            mobileCartCount.textContent = cart.reduce((sum, item) => sum + item.quantity, 0);
        } else if (mobileCheckoutBtn) {
            mobileCheckoutBtn.style.display = 'none';
        }

        emptyCart.style.display = 'none';
        
        let html = '';
        cart.forEach((item, index) => {
            const total = item.price * item.quantity;
            html += `
                <tr>
                    <td class="align-middle">
                        <div class="fw-bold text-dark">${item.name}</div>
                    </td>
                    <td class="align-middle">
                        <input type="number" class="form-control form-control-sm qty-input rounded-pill text-center mx-auto" 
                               data-id="${item.id}" value="${item.quantity}" min="1" style="width: 70px;">
                    </td>
                    <td class="align-middle text-end">KES ${item.price.toFixed(2)}</td>
                    <td class="align-middle text-end fw-bold">KES ${total.toFixed(2)}</td>
                    <td class="align-middle text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-circle remove-item px-2" data-id="${item.id}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        cartItems.innerHTML = html;
        
        // Add event listeners
        document.querySelectorAll('.qty-input').forEach(input => {
            input.addEventListener('change', function() {
                const productId = this.dataset.id;
                const newQty = parseInt(this.value) || 1;
                syncUpdateQuantity(productId, newQty);
            });
        });
        
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = parseInt(this.dataset.id);
                syncRemoveItem(productId);
            });
        });
        
        updateTotals();
    }

    // Update Totals
    function updateTotals() {
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        let discount = parseFloat(document.getElementById('discount').value) || 0;
        
        // Trade-in logic
        const tradeInAmount = tradeIns.reduce((sum, item) => sum + item.value, 0);
        document.getElementById('tradeInAmountText').textContent = `- KES ${tradeInAmount.toFixed(2)}`;

        // Handle Promotion
        const promoSelect = document.getElementById('promotion');
        const selectedOption = promoSelect.options[promoSelect.selectedIndex];
        const promoType = selectedOption.dataset.type;
        const promoValue = parseFloat(selectedOption.dataset.value) || 0;
        const minSpend = parseFloat(selectedOption.dataset.min) || 0;
        
        let promoDiscount = 0;
        if (subtotal >= minSpend) {
            if (promoType === 'percentage') {
                promoDiscount = (subtotal * promoValue) / 100;
            } else {
                promoDiscount = promoValue;
            }
        }
        
        const totalDiscount = discount + promoDiscount;
        const total = Math.max(0, subtotal - totalDiscount - tradeInAmount);
        
        document.getElementById('subtotal').textContent = `KES ${subtotal.toFixed(2)}`;
        document.getElementById('totalAmount').textContent = `KES ${total.toFixed(2)}`;
        
        // Update hidden inputs
        document.getElementById('subtotalInput').value = subtotal.toFixed(2);
        document.getElementById('taxInput').value = '0';
        document.getElementById('totalInput').value = total.toFixed(2);
        document.getElementById('cartData').value = JSON.stringify(cart);
        document.getElementById('tradeInData').value = JSON.stringify(tradeIns);
        document.getElementById('tradeInAmountInput').value = tradeInAmount.toFixed(2);
        
        calculateChange();
    }

    // Calculate Change
    function calculateChange() {
        const total = parseFloat(document.getElementById('totalInput').value) || 0;
        const tendered = parseFloat(document.getElementById('amountTendered').value) || 0;
        const change = tendered - total;
        
        document.getElementById('changeDue').textContent = `KES ${Math.max(0, change).toFixed(2)}`;
    }

    // Event Listeners
    document.getElementById('discount').addEventListener('input', updateTotals);
    document.getElementById('promotion').addEventListener('change', updateTotals);
    document.getElementById('amountTendered').addEventListener('input', calculateChange);
    
    document.getElementById('clearCart').addEventListener('click', function() {
        if (confirm('Are you sure you want to clear the cart?')) {
            syncClearCart();
        }
    });

    document.getElementById('cancelBtn').addEventListener('click', function() {
        if (cart.length > 0 && !confirm('Are you sure you want to cancel? All items will be cleared.')) {
            return;
        }
        syncClearCart();
        document.getElementById('posForm').reset();
    });

    // Mobile Checkout Scroll
    const mobileCheckoutBtn = document.getElementById('mobileCheckoutBtn');
    if (mobileCheckoutBtn) {
        mobileCheckoutBtn.addEventListener('click', function() {
            document.querySelector('.pos-right-col').scrollIntoView({ behavior: 'smooth' });
            // Pulse effect
            this.style.transform = 'scale(0.9)';
            setTimeout(() => this.style.transform = 'scale(1)', 100);
        });
    }

    // Payment Method Change
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const mpesaField = document.getElementById('mpesaPhoneField');
            const amountTenderedField = document.getElementById('amountTenderedField');
            const changeDueField = document.getElementById('changeDueField');
            const paymentMethodInput = document.getElementById('paymentMethod');
            
            // Sync with hidden input
            paymentMethodInput.value = this.value;
            
            if (this.value === 'mpesa') {
                mpesaField.style.display = 'block';
                document.getElementById('mpesaPhone').required = true;
                amountTenderedField.style.display = 'none';
                changeDueField.style.display = 'none';
            } else {
                mpesaField.style.display = 'none';
                document.getElementById('mpesaPhone').required = false;
                amountTenderedField.style.display = 'block';
                changeDueField.style.display = 'block';
            }
        });
    });

    // 4. Offline Queue & Sync Functions
    async function queueOfflineSale(formData) {
        const sale = {
            customer_id: formData.get('customer_id'),
            cart_data: formData.get('cart_data'),
            payment_method: formData.get('payment_method'),
            total_amount: formData.get('total_amount'),
            discount: formData.get('discount'),
            promotion_id: formData.get('promotion_id'),
            mpesa_phone: formData.get('mpesa_phone'),
            trade_in_data: formData.get('trade_in_data'),
            trade_in_amount: formData.get('trade_in_amount'),
            subtotal_amount: formData.get('subtotal_amount'),
            tax_amount: formData.get('tax_amount'),
            status: 'queued',
            timestamp: new Date().toISOString()
        };
        
        await db.sales_queue.add(sale);
        Swal.fire({
            title: 'Offline Mode',
            text: 'Sale queued locally and will sync when internet returns.',
            icon: 'info'
        });
        syncClearCart();
    }

    async function processSalesQueue() {
        if (!navigator.onLine) return;
        
        const queuedSales = await db.sales_queue.toArray();
        if (queuedSales.length === 0) return;

        console.log(`Syncing ${queuedSales.length} queued sales...`);
        
        for (const sale of queuedSales) {
            try {
                const formData = new FormData();
                for (const key in sale) {
                    if (key !== 'id' && key !== 'status' && key !== 'timestamp') {
                        formData.append(key, sale[key]);
                    }
                }
                
                const response = await fetch('{{ route("sales.store") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                    body: formData
                });
                
                if (response.ok) {
                    await db.sales_queue.delete(sale.id);
                    console.log('Sale synced successfully');
                }
            } catch (e) {
                console.error('Failed to sync sale:', e);
            }
        }
    }

    window.addEventListener('online', processSalesQueue);

    // Form Submission
    document.getElementById('posForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (cart.length === 0) {
            alert('Please add items to the cart before completing the sale.');
            return;
        }
        
        const total = parseFloat(document.getElementById('totalInput').value) || 0;
        const paymentMethod = document.getElementById('paymentMethod').value;

        // If Offline, Queue directly
        if (!navigator.onLine) {
            await queueOfflineSale(new FormData(this));
            return;
        }
        
        // Handle M-Pesa Payment
        if (paymentMethod === 'mpesa') {
            const phone = document.getElementById('mpesaPhone').value.trim();
            
            if (!phone) {
                alert('Please enter M-Pesa phone number');
                return;
            }
            await initiateMpesaPayment(phone, total);
            return;
        }
        const tendered = parseFloat(document.getElementById('amountTendered').value) || 0;
        if (tendered < total) {
            alert('Amount tendered is less than total amount.');
            return;
        }
        this.submit();
    });
    let mpesaCheckoutRequestId = null;
    let mpesaPollingInterval = null;
    async function initiateMpesaPayment(phone, amount) {
        try {
            const modal = new bootstrap.Modal(document.getElementById('mpesaModal'));
            showMpesaStatus('pending');
            modal.show();
            document.getElementById('mpesaPhoneDisplay').textContent = `Phone: ${phone}`;
            document.getElementById('mpesaAmountDisplay').textContent = `Amount: KES ${amount.toFixed(2)}`;
            const response = await fetch('/api/mpesa/initiate', {
    method: 'POST',
    credentials: 'include',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
        phone_number: phone,
        amount: amount
    })
});
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('text/html')) {
                const htmlResponse = await response.text();
                console.error('Received HTML response instead of JSON:', htmlResponse.substring(0, 500));
                showMpesaStatus('failed');
                document.getElementById('mpesaErrorMessage').textContent = 'Server error: API returned HTML. Check browser console for details.';
                return;
            }
            const data = await response.json();
            console.log('M-Pesa Response:', data)
            if (data.success) {
                console.log('STK Push initiated:', data);
                mpesaCheckoutRequestId = data.data.checkout_request_id;
                // Poll for payment status
                pollMpesaStatus(mpesaCheckoutRequestId);
            } else {
                console.error('STK Push failed:', data.message);
                showMpesaStatus('failed');
                document.getElementById('mpesaErrorMessage').textContent = data.message || 'Failed to initiate payment';
                if (data.debug_response) {
                    console.error('Debug response:', data.debug_response);
                }
            }
            
        } catch (error) {
            console.error('M-Pesa Error:', error);
            showMpesaStatus('failed');
            document.getElementById('mpesaErrorMessage').textContent = 'Error: ' + error.message;
        }
    }

    async function pollMpesaStatus(checkoutRequestId) {
        mpesaPollingInterval = setInterval(async () => {
            try {
                const response = await fetch(`/api/mpesa/status/${checkoutRequestId}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    if (data.status === 'confirmed') {
                        clearInterval(mpesaPollingInterval);
                        showMpesaStatus('success');
                        document.getElementById('mpesaReceiptNumber').textContent = 'Transaction ID: ' + (data.data.transaction_code || 'Confirmed');
                        document.getElementById('mpesaFinishBtn').style.display = 'block';
                    } else if (data.status === 'failed') {
                        clearInterval(mpesaPollingInterval);
                        showMpesaStatus('failed');
                        document.getElementById('mpesaErrorMessage').textContent = 'Payment was declined';
                    }
                }
            } catch (error) {
                console.error('Status polling error:', error);
            }
        }, 3000);
    }
    
    function showMpesaStatus(status) {
        document.getElementById('mpesaStatusPending').style.display = status === 'pending' ? 'block' : 'none';
        document.getElementById('mpesaStatusSuccess').style.display = status === 'success' ? 'block' : 'none';
        document.getElementById('mpesaStatusFailed').style.display = status === 'failed' ? 'block' : 'none';
    }
    document.getElementById('mpesaCancelBtn').addEventListener('click', function() {
        if (mpesaPollingInterval) {
            clearInterval(mpesaPollingInterval);
        }
        bootstrap.Modal.getInstance(document.getElementById('mpesaModal')).hide();
    });
    document.getElementById('mpesaFinishBtn').addEventListener('click', function() {
        document.getElementById('amountTendered').value = document.getElementById('totalInput').value;
        document.getElementById('posForm').submit();
    });
    const closeShiftModal = document.getElementById('closeShiftModal');
    if (closeShiftModal) {
        closeShiftModal.addEventListener('show.bs.modal', async function () {
            try {
                const response = await fetch('{{ route("shifts.active") }}');
                const data = await response.json();
                if (data.shift) {
                }
            } catch (e) { console.error(e); }
        });
    }
});
</script>
@endpush