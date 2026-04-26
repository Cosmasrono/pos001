@extends('layouts.app')

@section('title', 'Create Category')
@section('page-title', 'New Category')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-xl-6 col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-tag-plus me-2"></i>Create New Category
                    </h5>
                    <p class="text-muted small mt-1">Add a new category to organize your products</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-type text-muted"></i></span>
                                <input type="text" id="name" name="name" class="form-control border-start-0 @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" placeholder="e.g. Electronics, Beverages, etc." required autofocus>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" 
                                      placeholder="Briefly describe what this category includes...">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex gap-3 mt-5">
                            <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                <i class="bi bi-check-circle me-2"></i>Save Category
                            </button>
                            <a href="{{ route('categories.index') }}" class="btn btn-light px-4 border">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
