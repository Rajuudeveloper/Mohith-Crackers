@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center py-3 mb-4 border-bottom">
            <h1 class="h2">
                <i class="fas fa-edit me-2"></i>Edit Agent
            </h1>
            <a href="{{ route('agents.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Agents
            </a>
        </div>

        <div class="card">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Edit Agent Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('agents.update', $agent->id) }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Agent Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $agent->name) }}" 
                                   required
                                   placeholder="Enter agent name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="mobile_no" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                            <input type="tel" 
                                   class="form-control @error('mobile_no') is-invalid @enderror" 
                                   id="mobile_no" 
                                   name="mobile_no" 
                                   value="{{ old('mobile_no', $agent->mobile_no) }}" 
                                   required
                                   placeholder="Enter mobile number">
                            @error('mobile_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $agent->email) }}" 
                                   placeholder="Enter email address">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="opening_balance" class="form-label">Opening Balance <span class="text-danger">*</span></label>
                            <input type="number" 
                                   step="0.01" 
                                   class="form-control @error('opening_balance') is-invalid @enderror" 
                                   id="opening_balance" 
                                   name="opening_balance" 
                                   value="{{ old('opening_balance', $agent->opening_balance) }}" 
                                   required
                                   placeholder="0.00">
                            @error('opening_balance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" 
                                  name="address" 
                                  rows="3" 
                                  placeholder="Enter full address">{{ old('address', $agent->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('agents.index') }}" class="btn btn-outline-secondary me-md-2">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Agent
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection