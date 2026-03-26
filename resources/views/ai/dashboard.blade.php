@extends('layouts.app')

@section('title', 'AI Decision Center')
@section('page-title', 'AI Decision Center')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Summary Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white shadow-lg border-0 overflow-hidden glass-card">
                <div class="card-body p-4 position-relative">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-8">
                            <h3 class="fw-bold mb-1"><i class="bi bi-robot me-2"></i>AI Decision Center</h3>
                            <p class="mb-0 opacity-75">Autonomous inventory optimization powered by artificial intelligence</p>
                            <div class="mt-3 p-3 rounded bg-white bg-opacity-10 border border-white border-opacity-20 backdrop-blur">
                                <i class="bi bi-chat-left-dots me-2"></i><strong>AI Executive Summary:</strong> {{ $globalAIInsight }}
                            </div>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <div class="badge bg-white text-primary px-3 px-md-4 py-2 py-md-3 rounded-pill shadow-sm fs-6 fs-md-5">
                                <i class="bi bi-cpu me-1"></i> {{ count($reorderRecommendations) + count($pricingRecommendations) + count($wasteRisks) }} Decisions
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Briefing -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm glass-card" style="border-left: 5px solid #6366f1 !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                            <i class="bi bi-briefcase fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">Daily AI Executive Briefing</h5>
                            <small class="text-muted">{{ now()->format('l, F j, Y') }}</small>
                        </div>
                    </div>
                    <div class="bg-light bg-opacity-50 p-4 rounded-4 border border-info border-opacity-10">
                        <p class="mb-0 fs-5 lh-lg font-outfit text-dark">{{ $dailyBriefing }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Decision Categories Navigation -->
    <div class="row mb-4 g-3">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-lift glass-card">
                <div class="card-body text-center p-4">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-3 d-inline-flex mb-3">
                        <i class="bi bi-box-seam fs-1"></i>
                    </div>
                    <h2 class="fw-bold text-gradient-danger">{{ count($reorderRecommendations) }}</h2>
                    <p class="text-muted mb-0 fw-medium">Reorder Decisions</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-lift glass-card">
                <div class="card-body text-center p-4">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 d-inline-flex mb-3">
                        <i class="bi bi-tag fs-1"></i>
                    </div>
                    <h2 class="fw-bold text-gradient-success">{{ count($pricingRecommendations) }}</h2>
                    <p class="text-muted mb-0 fw-medium">Pricing Opportunities</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-lift glass-card">
                <div class="card-body text-center p-4">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 d-inline-flex mb-3">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                    <h2 class="fw-bold text-gradient-warning">{{ count($wasteRisks) }}</h2>
                    <p class="text-muted mb-0 fw-medium">Waste Risks</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-lift glass-card">
                <div class="card-body text-center p-4">
                    <div class="bg-info bg-opacity-10 text-info rounded-circle p-3 d-inline-flex mb-3">
                        <i class="bi bi-box2-heart fs-1"></i>
                    </div>
                    <h2 class="fw-bold text-gradient-info">{{ count($bundleSuggestions) }}</h2>
                    <p class="text-muted mb-0 fw-medium">Bundle Suggestions</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== BRANCH SALES LINK CARD ===================== -->
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('ai.branch-sales') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm glass-card hover-lift" style="background: linear-gradient(135deg, rgba(14,165,233,0.08) 0%, rgba(99,102,241,0.08) 100%) !important;">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="rounded-4 p-2 p-md-3 shadow-sm text-white" style="background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);">
                                    <i class="bi bi-shop fs-3 fs-md-2"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h5 class="fw-bold mb-1 text-dark font-outfit">Branch Sales Intelligence</h5>
                                <p class="mb-0 text-muted small">View top-selling products per branch, revenue comparisons, Cash vs Mpesa breakdown, and AI-powered branch performance insights.</p>
                            </div>
                            <div class="col-auto">
                                <span class="badge rounded-pill px-4 py-2 text-white fw-semibold" style="background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);">
                                    <i class="bi bi-arrow-right me-1"></i> View Analysis
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <!-- ================================================================= -->

    <!-- Reorder Recommendations -->
    @if(count($reorderRecommendations) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 glass-card">
                <div class="card-header bg-white bg-opacity-50 border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-box-seam text-danger me-2"></i>Smart Reorder Decisions
                        <span class="badge bg-danger bg-opacity-10 text-danger ms-2">{{ count($reorderRecommendations) }} urgent</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Product</th>
                                    <th>Stock</th>
                                    <th class="d-none d-md-table-cell">Reorder Point</th>
                                    <th class="d-none d-md-table-cell">Suggested Qty</th>
                                    <th>Stockout</th>
                                    <th class="d-none d-sm-table-cell">Urgency</th>
                                    <th class="text-end pe-4">Decision</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reorderRecommendations as $item)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">{{ $item['product']->name }}</div>
                                            <small class="text-muted">SKU: {{ $item['product']->sku }}</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $item['recommendation']['urgency'] === 'high' ? 'bg-danger' : 'bg-warning' }}">
                                                {{ $item['product']->quantity_in_stock }}
                                            </span>
                                        </td>
                                        <td class="d-none d-md-table-cell">{{ $item['recommendation']['reorder_point'] }}</td>
                                        <td class="d-none d-md-table-cell">
                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                +{{ $item['recommendation']['recommended_qty'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="{{ $item['recommendation']['days_to_stockout'] <= 3 ? 'text-danger' : 'text-warning' }} small">
                                                {{ $item['recommendation']['days_to_stockout'] }}d
                                            </strong>
                                        </td>
                                        <td class="d-none d-sm-table-cell">
                                            @if($item['recommendation']['urgency'] === 'high')
                                                <span class="badge bg-danger">CRITICAL</span>
                                            @elseif($item['recommendation']['urgency'] === 'medium')
                                                <span class="badge bg-warning">Medium</span>
                                            @else
                                                <span class="badge bg-info">Low</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-danger rounded-pill px-3 me-1" onclick="executeReorder({{ $item['product']->id }}, {{ $item['recommendation']['recommended_qty'] }})">
                                                <i class="bi bi-cart-plus me-1"></i> Auto-Reorder
                                            </button>
                                            <a href="{{ route('ai.product', $item['product']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Pricing Optimization -->
    @if(count($pricingRecommendations) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 glass-card">
                <div class="card-header bg-white bg-opacity-50 border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-tag text-success me-2"></i>AI Pricing Optimization
                        <span class="badge bg-success bg-opacity-10 text-success ms-2">{{ count($pricingRecommendations) }} opportunities</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Product</th>
                                    <th>Price</th>
                                    <th class="d-none d-sm-table-cell">Change</th>
                                    <th class="d-none d-md-table-cell">Reasoning</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pricingRecommendations as $item)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">{{ $item['product']->name }}</div>
                                            <small class="text-muted">Stock: {{ $item['product']->quantity_in_stock }}</small>
                                        </td>
                                        <td><strong>KSh {{ number_format($item['pricing']['current_price'], 2) }}</strong></td>
                                        <td>
                                            <strong class="{{ $item['pricing']['price_change'] > 0 ? 'text-success' : 'text-danger' }}">
                                                KSh {{ number_format($item['pricing']['suggested_price'], 2) }}
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="badge {{ $item['pricing']['price_change'] > 0 ? 'bg-success' : 'bg-danger' }}">
                                                {{ $item['pricing']['price_change'] > 0 ? '+' : '' }}{{ $item['pricing']['price_change_percentage'] }}%
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $item['pricing']['reason'] }}</small>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 10px; border-radius: 5px;">
                                                <div class="progress-bar bg-success" style="width: {{ $item['pricing']['confidence'] }}%"></div>
                                            </div>
                                            <small class="text-muted fw-bold">{{ $item['pricing']['confidence'] }}% match</small>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-success rounded-pill px-4 shadow-sm" onclick="applyPricing({{ $item['product']->id }}, {{ $item['pricing']['suggested_price'] }})">
                                                <i class="bi bi-check2 me-1"></i> Apply
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row mb-4">
        <!-- Trending Analysis -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 glass-card">
                <div class="card-header bg-white bg-opacity-50 border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-graph-up-arrow text-success me-2"></i>Trending Up
                    </h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush bg-transparent">
                        @forelse($trendingUp as $item)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 bg-transparent border-opacity-10">
                                <div>
                                    <div class="fw-bold text-dark">{{ $item['product']->name }}</div>
                                    <small class="text-muted">High demand predicted</small>
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success fw-bold p-2 px-3 rounded-pill">
                                    <i class="bi bi-arrow-up"></i> +{{ number_format($item['trend'], 1) }}%
                                </span>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-4 text-muted bg-transparent">
                                No upward trends detected
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Slow Moving Items -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 glass-card">
                <div class="card-header bg-white bg-opacity-50 border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-hourglass text-warning me-2"></i>Slow-Moving Items
                    </h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush bg-transparent">
                        @forelse($slowMoving as $item)
                            <li class="list-group-item px-4 py-3 bg-transparent border-opacity-10">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $item->name }}</div>
                                        <small class="text-muted">{{ $item->quantity_in_stock }} in stock</small>
                                    </div>
                                    <button class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="createPromotion({{ $item->id }})">
                                        <i class="bi bi-megaphone me-1"></i> Promote
                                    </button>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-4 text-muted bg-transparent">
                                All items moving well
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Waste Risk Management -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 glass-card">
                <div class="card-header bg-white bg-opacity-50 border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-exclamation-triangle text-danger me-2"></i>Waste Risks
                    </h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush bg-transparent">
                        @forelse($wasteRisks as $item)
                            <li class="list-group-item px-4 py-3 bg-transparent border-opacity-10">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark">{{ $item['product']->name }}</div>
                                        <small class="text-danger">
                                            <i class="bi bi-calendar-x"></i>
                                            {{ $item['risk']['days_until_expiry'] }} days to expiry
                                        </small>
                                    </div>
                                    <span class="badge bg-{{ $item['risk']['risk_level'] === 'critical' ? 'danger' : ($item['risk']['risk_level'] === 'high' ? 'warning' : 'info') }} bg-opacity-10 text-{{ $item['risk']['risk_level'] === 'critical' ? 'danger' : ($item['risk']['risk_level'] === 'high' ? 'warning' : 'info') }} rounded-pill px-3">
                                        {{ strtoupper($item['risk']['risk_level']) }}
                                    </span>
                                </div>
                                <div class="p-3 bg-danger bg-opacity-5 rounded-3 border border-danger border-opacity-10">
                                    <small class="text-danger d-block fw-bold mb-1">{{ $item['risk']['units_at_risk'] }} units at risk</small>
                                    <small class="text-muted">{{ $item['risk']['action'] }}</small>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-4 text-muted bg-transparent">
                                No waste risks detected
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>



    <a href="{{ route('ai.pricing-health') }}" class="text-decoration-none">
    <div class="card border-0 shadow-sm glass-card hover-lift" style="background: linear-gradient(135deg, rgba(16,185,129,0.08) 0%, rgba(245,158,11,0.08) 100%) !important;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="rounded-4 p-3 shadow-sm text-white" style="background: linear-gradient(135deg, #10b981 0%, #f59e0b 100%);">
                        <i class="bi bi-currency-dollar fs-2"></i>
                    </div>
                </div>
                <div class="col">
                    <h5 class="fw-bold mb-1 text-dark font-outfit">Pricing Health Analysis</h5>
                    <p class="mb-0 text-muted small">Identify overpriced and underpriced products based on sales velocity, margin, and demand trends.</p>
                </div>
                <div class="col-auto">
                    <span class="badge rounded-pill px-4 py-2 text-white fw-semibold" style="background: linear-gradient(135deg, #10b981 0%, #f59e0b 100%);">
                        <i class="bi bi-arrow-right me-1"></i> View Analysis
                    </span>
                </div>
            </div>
        </div>
    </div>
</a>
    <!-- Product Bundle Suggestions -->
    @if(count($bundleSuggestions) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 glass-card overflow-hidden">
                <div class="card-header bg-white bg-opacity-50 border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-box2-heart text-info me-2"></i>AI Bundle Suggestions
                        <span class="badge bg-info bg-opacity-10 text-info ms-2">Boost revenue 15-25%</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Bundle Components</th>
                                    <th>Bought Together</th>
                                    <th>Individual Price</th>
                                    <th>Suggested Bundle Price</th>
                                    <th>Savings</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bundleSuggestions as $bundle)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $bundle['product1'] }}</div>
                                            <div class="text-muted small">+ {{ $bundle['product2'] }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">{{ $bundle['frequency'] }} times</span>
                                        </td>
                                        <td>KSh {{ number_format($bundle['individual_total'], 2) }}</td>
                                        <td>
                                            <strong class="text-success fs-5">KSh {{ number_format($bundle['suggested_bundle_price'], 2) }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">
                                                Save KSh {{ number_format($bundle['discount_amount'], 2) }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-info text-white rounded-pill px-4 shadow-sm" onclick="createBundle('{{ $bundle['product1'] }}', '{{ $bundle['product2'] }}')">
                                                <i class="bi bi-plus-circle me-1"></i> Create Bundle
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- AI Insights & Tips -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 glass-card" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);">
                <div class="card-body p-4">
                    <div class="d-flex">
                        <div class="me-3">
                            <div class="bg-primary text-white rounded-4 p-3 shadow-sm">
                                <i class="bi bi-lightbulb fs-2"></i>
                            </div>
                        </div>
                        <div>
                            <h5 class="fw-bold text-primary mb-2">AI Optimization Tip</h5>
                            <p class="mb-0 text-muted lh-base">Products with upward trends should have safety stock increased by 20% to prevent stockouts during demand spikes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 glass-card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);">
                <div class="card-body p-4">
                    <div class="d-flex">
                        <div class="me-3">
                            <div class="bg-success text-white rounded-4 p-3 shadow-sm">
                                <i class="bi bi-graph-up fs-2"></i>
                            </div>
                        </div>
                        <div>
                            <h5 class="fw-bold text-success mb-2">Revenue Opportunity</h5>
                            <p class="mb-0 text-muted lh-base">Implementing AI-suggested bundles and dynamic pricing can increase overall revenue by 18-30% based on current data.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function executeReorder(productId, quantity) {
    if (confirm(`Auto-reorder ${quantity} units?`)) {
        fetch('/api/ai/execute-recommendation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                action_type: 'reorder',
                product_id: productId,
                parameters: { quantity: quantity }
            })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        });
    }
}

function applyPricing(productId, newPrice) {
    if (confirm(`Update price to KSh ${newPrice}?`)) {
        fetch('/api/ai/execute-recommendation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                action_type: 'price_change',
                product_id: productId,
                parameters: { new_price: newPrice }
            })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        });
    }
}

function createBundle(product1, product2) {
    alert('Bundle creation feature - integrate with your bundle management system');
}

function createPromotion(productId) {
    alert('Promotion creation feature - integrate with your promotion system');
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

.font-outfit {
    font-family: 'Outfit', sans-serif;
}

.glass-card {
    background: rgba(255, 255, 255, 0.7) !important;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
}

.backdrop-blur {
    backdrop-filter: blur(5px);
}

.hover-lift {
    transition: all 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
}

.text-gradient-danger {
    background: linear-gradient(135deg, #ef4444 0%, #991b1b 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.text-gradient-success {
    background: linear-gradient(135deg, #10b981 0%, #065f46 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.text-gradient-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #92400e 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.text-gradient-info {
    background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

body {
    background-color: #f8fafc;
    font-family: 'Inter', sans-serif;
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'Outfit', sans-serif;
}

.badge-outline-primary {
    border: 1px solid rgba(99, 102, 241, 0.3);
    color: #6366f1;
    background: rgba(99, 102, 241, 0.05);
}
</style>
@endsection