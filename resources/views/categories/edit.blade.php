@extends('layouts.app')

@section('title', 'Edit Category')
@section('page-title', 'Edit Category')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-xl-6 col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-pencil-square me-2"></i>Edit Category: {{ $category->name }}
                    </h5>
                    <p class="text-muted small mt-1">Update category details and description</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('categories.update', $category->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-type text-muted"></i></span>
                                <input type="text" id="name" name="name" class="form-control border-start-0 @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $category->name) }}" required autofocus>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" 
                                      placeholder="Briefly describe what this category includes...">{{ old('description', $category->description) }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex gap-3 mt-5">
                            <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                <i class="bi bi-check-circle me-2"></i>Update Category
                            </button>
                            <a href="{{ route('categories.index') }}" class="btn btn-light px-4 border">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            @if($category->products_count > 0)
                <div class="alert alert-info border-0 shadow-sm mt-4">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    This category currently contains <strong>{{ $category->products_count }}</strong> products.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
