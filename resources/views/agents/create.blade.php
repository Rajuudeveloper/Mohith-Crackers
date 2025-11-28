@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add New Agent</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('agents.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Agents
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-plus me-2"></i>Agent Information
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('agents.store') }}" method="POST" id="agentForm">
                    @csrf
                    
                    <div class="row">
                        <!-- Name Field -->
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">
                                Agent Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required
                                   placeholder="Enter agent full name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Mobile Number Field -->
                        <div class="col-md-6 mb-3">
                            <label for="mobile_no" class="form-label">
                                Mobile Number <span class="text-danger">*</span>
                            </label>
                            <input type="tel" 
                                   class="form-control @error('mobile_no') is-invalid @enderror" 
                                   id="mobile_no" 
                                   name="mobile_no" 
                                   value="{{ old('mobile_no') }}" 
                                   required
                                   placeholder="Enter 10-digit mobile number"
                                   pattern="[0-9]{10}"
                                   maxlength="10">
                            @error('mobile_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter 10-digit mobile number without country code</div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Email Field -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="Enter email address">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Opening Balance Field -->
                        <div class="col-md-6 mb-3">
                            <label for="opening_balance" class="form-label">
                                Opening Balance <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" 
                                       step="0.01" 
                                       min="0"
                                       class="form-control @error('opening_balance') is-invalid @enderror" 
                                       id="opening_balance" 
                                       name="opening_balance" 
                                       value="{{ old('opening_balance', 0) }}" 
                                       required
                                       placeholder="0.00">
                                @error('opening_balance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Enter the initial balance for this agent</div>
                        </div>
                    </div>

                    <!-- Address Field -->
                    <div class="mb-4">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" 
                                  name="address" 
                                  rows="4" 
                                  placeholder="Enter complete address with city, state, and pincode">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Form Buttons -->
                    <div class="d-flex justify-content-between align-items-center border-top pt-4">
                        <a href="{{ route('agents.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Close
                        </a>
                        
                        <div>
                            <button type="reset" class="btn btn-outline-danger me-2">
                                <i class="fas fa-redo me-1"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create Agent
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('agentForm');
        const resetButton = form.querySelector('button[type="reset"]');
        
        // Reset form confirmation
        resetButton.addEventListener('click', function(e) {
            if (form.checkValidity()) {
                if (!confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
                    e.preventDefault();
                }
            }
        });

        // Mobile number validation
        const mobileInput = document.getElementById('mobile_no');
        mobileInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });

        // Form submission enhancement
        form.addEventListener('submit', function() {
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creating...';
        });
    });
</script>

<style>
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .card {
        border: 1px solid rgba(0, 0, 0, 0.125);
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .btn {
        border-radius: 0.375rem;
        font-weight: 500;
    }
    
    .form-label {
        font-weight: 500;
        color: #495057;
    }
    
    .input-group-text {
        background-color: #f8f9fa;
        border-color: #ced4da;
    }
</style>
@endpush