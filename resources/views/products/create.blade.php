@extends('layouts.app')

@section('title', 'Create Product')
@section('page-title', 'Add New Product')

@section('content')
@php
    $userBranch = auth()->user()->branch;
    // Multi-branch allocator: SuperAdmin (no branch_id), Owner role, or platform admin.
    // Falls back to the controller-passed flag, but stays safe if rendered directly.
    $isMain = $canAllocateAcrossBranches ?? (!auth()->user()->branch_id);
    $hasBranches = $branches->isNotEmpty();
@endphp

<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-xl-10 mx-auto">
            {{-- Status Alert --}}
            @if($isMain)
                <div class="alert alert-info d-flex align-items-center mb-4 border-0 shadow-sm" style="background: var(--info-light); border-left: 5px solid var(--info) !important;">
                    <div class="rounded-circle bg-white p-2 me-3 shadow-sm">
                        <i class="bi bi-building-fill text-info fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-info">Main Account (SuperAdmin)</h6>
                        <p class="mb-0 small opacity-75">You are creating products for the <strong>Main</strong> branch. Stock will be distributed across branches.</p>
                    </div>
                </div>
            @else
                <div class="alert alert-primary d-flex align-items-center mb-4 border-0 shadow-sm" style="background: var(--primary-light); border-left: 5px solid var(--primary) !important;">
                    <div class="rounded-circle bg-white p-2 me-3 shadow-sm">
                        <i class="bi bi-geo-alt-fill text-primary fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-primary">Branch Account: {{ $userBranch->name }}</h6>
                        <p class="mb-0 small opacity-75">Stock will be allocated strictly to your branch only.</p>
                    </div>
                </div>
            @endif

            <form action="{{ route('products.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    {{-- Left Column: Product Details --}}
                    <div class="col-lg-8">
                        {{-- Basic Information Card --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h5 class="mb-0 fw-bold text-primary">
                                    <i class="bi bi-info-circle me-2"></i>Basic Information
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-7">
                                        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-tag text-muted"></i></span>
                                            <input type="text" id="name" name="name" class="form-control border-start-0 @error('name') is-invalid @enderror" 
                                                   value="{{ old('name') }}" placeholder="Enter product name" required>
                                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-grid text-muted"></i></span>
                                            <select name="category_id" id="category_id" class="form-select border-start-0 border-end-0 @error('category_id') is-invalid @enderror" required>
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-outline-primary border-2" id="addCategoryBtn"
                                                    data-bs-toggle="modal" data-bs-target="#categoryModal"
                                                    title="Add new category">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="sku" class="form-label">SKU (System Generated)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-robot text-muted"></i></span>
                                           <input type="text" id="sku" name="sku" class="form-control border-start-0 border-end-0 bg-light fw-bold text-primary" 
       value="" placeholder="e.g. 130326-142507-384921" readonly>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="barcode" class="form-label">Barcode</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-upc-scan text-muted"></i></span>
                                            <input type="text" id="barcode" name="barcode" class="form-control border-start-0 @error('barcode') is-invalid @enderror" 
                                                   value="{{ old('barcode') }}" placeholder="Scan or enter barcode">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea name="description" rows="3" class="form-control" placeholder="Optional product description...">{{ old('description') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Pricing Section --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h5 class="mb-0 fw-bold text-success">
                                    <i class="bi bi-currency-dollar me-2"></i>Pricing & Costs
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="cost_price" class="form-label">Cost Price (KES)</label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-white border-end-0 text-muted small">KES</span>
                                            <input type="number" step="0.01" id="cost_price" name="cost_price" 
                                                   class="form-control border-start-0 fw-bold text-muted" 
                                                   value="{{ old('cost_price') }}" placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="selling_price" class="form-label text-success fw-bold">Selling Price (KES) *</label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-success-light border-success border-end-0 text-success fw-bold small">KES</span>
                                            <input type="number" step="0.01" id="selling_price" name="selling_price" 
                                                   class="form-control border-success border-start-0 fw-bold text-success" 
                                                   value="{{ old('selling_price') }}" required placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Inventory & Allocation --}}
                    <div class="col-lg-4">
                        {{-- Inventory Settings --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h6 class="mb-0 fw-bold text-warning">
                                    <i class="bi bi-shield-exclamation me-2"></i>Settings
                                </h6>
                            </div>
                            <div class="card-body p-4">
                                <label for="reorder_level" class="form-label">Reorder Level *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-bell text-warning"></i></span>
                                    <input type="number" id="reorder_level" name="reorder_level" class="form-control" 
                                           value="{{ old('reorder_level', 10) }}" required min="0">
                                </div>
                                <small class="text-muted mt-1 d-block">Alert me when stock falls below this.</small>
                            </div>
                        </div>

                        {{-- Stock Distribution Card --}}
                        <div class="card border-0 shadow-sm" style="border-top: 5px solid var(--primary) !important;">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h5 class="mb-0 fw-bold">
                                    <i class="bi bi-boxes me-2"></i>Stock Distribution
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                @if($isMain)
                                    <div class="mb-4">
                                        <label for="total_stock" class="form-label fw-bold">Grand Total Stock <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-primary text-white border-primary"><i class="bi bi-plus-square"></i></span>
                                            <input type="number" id="total_stock" name="total_stock" 
                                                   class="form-control border-primary fw-bold" 
                                                   value="{{ old('total_stock', 0) }}" min="0" required>
                                        </div>
                                        <small class="text-muted mt-2 d-block">Enter total units purchased. Allocate to branches below — remainder stays in Main Branch.</small>
                                    </div>

                                    <hr class="my-4 opacity-10">

                                    <p class="text-muted small mb-3">Distribute the total units below:</p>
                                    
                                    <div class="branch-inputs overflow-auto" style="max-height: 450px; padding-right: 5px;">
                                        @forelse ($branches as $branch)
                                            @php
                                                $isMainBranch = (bool) $branch->is_main;
                                            @endphp
                                            <div class="mb-3 p-3 {{ $isMainBranch ? 'bg-primary-light border-primary main-branch-card' : 'bg-light other-branch-card' }} rounded-3 transition-hover border hover-border-primary position-relative">
                                                @if($isMainBranch)
                                                    <span class="position-absolute top-0 end-0 mt-2 me-2">
                                                        <i class="bi bi-star-fill text-primary" title="Main Store/Source"></i>
                                                    </span>
                                                @endif
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="fw-bold small {{ $isMainBranch ? 'text-primary' : 'text-dark' }} d-flex align-items-center gap-2">
                                                        <i class="bi {{ $isMainBranch ? 'bi-house-heart-fill' : 'bi-geo-alt' }} opacity-50"></i>
                                                        {{ $branch->name }}
                                                        @if($isMainBranch)
                                                            <span class="badge bg-primary text-white" style="font-size: 0.6rem;">AUTO-ALLOCATED</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="input-group input-group-sm">
                                                    <input type="number"
                                                           name="branch_quantities[{{ $branch->id }}]"
                                                           class="form-control branch-qty text-center fw-bold shadow-none {{ $isMainBranch ? 'main-branch-qty bg-white' : 'other-branch-qty' }}"
                                                           min="0"
                                                           {{ $isMainBranch ? 'readonly' : '' }}
                                                           value="{{ old('branch_quantities.' . $branch->id, 0) }}"
                                                           placeholder="0">
                                                    <span class="input-group-text bg-white text-muted">Units</span>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-4 px-3 bg-info-light rounded-3 border border-info">
                                                <i class="bi bi-magic fs-1 text-info"></i>
                                                <h6 class="fw-bold text-info mt-2 mb-1">No Branches Yet</h6>
                                                <p class="text-muted small mb-2">No worries — when you save this product, a <strong>Main Branch</strong> will be created automatically and your stock will be allocated to it.</p>
                                                <small class="text-muted">You can add more branches later from the Branches section.</small>
                                            </div>
                                        @endforelse
                                    </div>

                                    <div id="allocation-error" class="alert alert-danger mt-3 d-none">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        Branch allocations exceed total stock!
                                    </div>
                                @else
                                    {{-- Single Branch Allocation (No changes needed for branch users) --}}
                                    <div class="text-center py-3">
                                        <div class="rounded-circle bg-primary-light d-inline-flex p-3 mb-3">
                                            <i class="bi bi-box-seam text-primary fs-3"></i>
                                        </div>
                                        <label for="branch_single_qty" class="form-label d-block fw-bold mb-3">
                                            Units for {{ $userBranch->name }}
                                        </label>
                                        <div class="input-group input-group-lg justify-content-center mx-auto" style="max-width: 200px;">
                                            <input type="number"
                                                   id="branch_single_qty"
                                                   name="branch_quantities[{{ $userBranch->id }}]"
                                                   class="form-control text-center fw-bold rounded-start"
                                                   min="0"
                                                   value="{{ old('branch_quantities.' . $userBranch->id, 0) }}"
                                                   placeholder="0">
                                            <span class="input-group-text bg-white text-muted">QTY</span>
                                        </div>
                                        <p class="text-muted small mt-3 px-2">Stock will be exclusively managed by your branch.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex gap-3 mt-5 pb-5">
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow-lg">
                        <i class="bi bi-check-circle me-2"></i>Save Product
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-light btn-lg px-4 border">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .transition-hover {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .hover-border-primary:hover {
        border-color: var(--primary) !important;
        transform: translateY(-2px);
        background: white !important;
        box-shadow: var(--shadow-sm);
    }
    .bg-primary-light { background: var(--primary-light) !important; }
    .bg-success-light { background: var(--success-light) !important; }
    .input-group-text { border: 2px solid var(--border-color); }
    .form-control { border: 2px solid var(--border-color); }
    .card { border-radius: 20px !important; }
    .input-group:focus-within .input-group-text {
        border-color: var(--primary);
        color: var(--primary) !important;
    }
</style>

{{-- Add Category Modal --}}
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px !important;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-light p-2">
                        <i class="bi bi-grid-fill text-primary fs-5"></i>
                    </div>
                    <h5 class="modal-title fw-bold mb-0" id="categoryModalLabel">New Category</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div id="cat-error" class="alert alert-danger d-none py-2 small"></div>
                <div class="mb-3">
                    <label for="cat-name" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-tag text-muted"></i></span>
                        <input type="text" id="cat-name" class="form-control border-start-0"
                               placeholder="e.g. Beverages, Electronics…" maxlength="255">
                    </div>
                </div>
                <div class="mb-1">
                    <label for="cat-desc" class="form-label fw-semibold">Description <span class="text-muted small fw-normal">(optional)</span></label>
                    <textarea id="cat-desc" class="form-control" rows="2" placeholder="Short description…" maxlength="500"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="saveCategoryBtn" class="btn btn-primary px-4">
                    <span id="catBtnText"><i class="bi bi-check-circle me-1"></i>Save Category</span>
                    <span id="catBtnSpinner" class="d-none spinner-border spinner-border-sm"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const totalStockInput = document.getElementById('total_stock');
    const mainBranchQty   = document.querySelector('.main-branch-qty');
    const otherBranchQtys = document.querySelectorAll('.other-branch-qty');
    const allBranchQtys   = document.querySelectorAll('.branch-qty');
    const errorAlert      = document.getElementById('allocation-error');

    function updateFromTotal() {
        if (!totalStockInput) return;
        const total = parseInt(totalStockInput.value || 0, 10);
        let allocated = 0;
        otherBranchQtys.forEach(inp => { allocated += parseInt(inp.value || 0, 10); });

        // No main branch flagged — just validate allocations don't exceed total.
        if (!mainBranchQty) {
            if (allocated > total) {
                errorAlert?.classList.remove('d-none');
            } else {
                errorAlert?.classList.add('d-none');
            }
            return;
        }

        const mainShare = total - allocated;
        if (mainShare < 0) {
            mainBranchQty.value = 0;
            mainBranchQty.closest('.main-branch-card').classList.add('border-danger');
            errorAlert.classList.remove('d-none');
        } else {
            mainBranchQty.value = mainShare;
            mainBranchQty.closest('.main-branch-card').classList.remove('border-danger');
            errorAlert.classList.add('d-none');
        }
    }

    if (totalStockInput) {
        totalStockInput.addEventListener('input', updateFromTotal);
        otherBranchQtys.forEach(inp => { inp.addEventListener('input', updateFromTotal); });
        updateFromTotal();
    }

    // ── Category quick-add modal ──────────────────────────────────────────────
    const categoryModal  = document.getElementById('categoryModal');
    const catNameInput   = document.getElementById('cat-name');
    const catDescInput   = document.getElementById('cat-desc');
    const catErrorBox    = document.getElementById('cat-error');
    const saveCategoryBtn = document.getElementById('saveCategoryBtn');
    const catBtnText     = document.getElementById('catBtnText');
    const catBtnSpinner  = document.getElementById('catBtnSpinner');
    const categorySelect = document.getElementById('category_id');

    if (categoryModal) {
        categoryModal.addEventListener('shown.bs.modal', () => catNameInput.focus());
        categoryModal.addEventListener('hidden.bs.modal', () => {
            catNameInput.value = '';
            catDescInput.value = '';
            catErrorBox.classList.add('d-none');
            catErrorBox.textContent = '';
        });

        catNameInput.addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); saveCategoryBtn.click(); }
        });

        saveCategoryBtn.addEventListener('click', async () => {
            const name = catNameInput.value.trim();
            if (!name) {
                catErrorBox.textContent = 'Category name is required.';
                catErrorBox.classList.remove('d-none');
                catNameInput.focus();
                return;
            }

            catBtnText.classList.add('d-none');
            catBtnSpinner.classList.remove('d-none');
            saveCategoryBtn.disabled = true;
            catErrorBox.classList.add('d-none');

            try {
                const resp = await fetch('{{ route('categories.quick-store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                                     ?? '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ name, description: catDescInput.value.trim() }),
                });

                const data = await resp.json();

                if (!resp.ok) {
                    const msg = data.errors?.name?.[0] ?? data.message ?? 'Could not save category.';
                    catErrorBox.textContent = msg;
                    catErrorBox.classList.remove('d-none');
                    return;
                }

                // Add to select and auto-select the new category
                const opt = new Option(data.name, data.id, true, true);
                categorySelect.add(opt);
                categorySelect.dispatchEvent(new Event('change'));

                bootstrap.Modal.getInstance(categoryModal).hide();
            } catch {
                catErrorBox.textContent = 'Network error. Please try again.';
                catErrorBox.classList.remove('d-none');
            } finally {
                catBtnText.classList.remove('d-none');
                catBtnSpinner.classList.add('d-none');
                saveCategoryBtn.disabled = false;
            }
        });
    }
});
</script>
<style>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}
.animate-shake { animation: shake 0.2s ease-in-out 0s 2; }
</style>
@endpush
@endsection