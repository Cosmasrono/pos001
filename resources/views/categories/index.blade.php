@extends('layouts.app')

@section('title', 'Categories')
@section('page-title', 'Product Categories')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Manage Categories</h4>
            <p class="text-muted small mb-0">Organize your products with categories</p>
        </div>
        <a href="{{ route('categories.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>New Category
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Category Name</th>
                            <th>Description</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary-light p-2 me-3">
                                            <i class="bi bi-tag-fill text-primary"></i>
                                        </div>
                                        <span class="fw-bold">{{ $category->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $category->description ?: 'No description provided' }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                <i class="bi bi-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="opacity-25 mb-3">
                                        <i class="bi bi-tags" style="font-size: 4rem;"></i>
                                    </div>
                                    <h5 class="text-muted">No categories found</h5>
                                    <p class="text-muted small">Start by creating your first product category</p>
                                    <a href="{{ route('categories.create') }}" class="btn btn-primary mt-3">
                                        <i class="bi bi-plus-lg me-2"></i>Create Category
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-light { background-color: rgba(99, 102, 241, 0.1) !important; }
    .bg-info-light { background-color: rgba(6, 182, 212, 0.1) !important; }
</style>
@endsection
