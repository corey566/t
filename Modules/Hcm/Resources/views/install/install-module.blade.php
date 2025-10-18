
@extends('layouts.install')

@section('title', 'Install HCM Module')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-building"></i>
                        Install {{ $module_display_name ?? 'HCM Module' }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Module Information</h5>
                        <ul class="mb-0">
                            <li><strong>Module:</strong> Havelock City Mall Integration</li>
                            <li><strong>Platform:</strong> Ultimate Forester POS</li>
                            <li><strong>Description:</strong> Connects your POS with HCM API for real-time invoice sync and reporting</li>
                            <li><strong>Version:</strong> {{ config('hcm.module_version', '1.0.0') }}</li>
                        </ul>
                    </div>

                    @if(session('status'))
                        <div class="alert alert-{{ session('status.success') ? 'success' : 'danger' }}">
                            {{ session('status.msg') }}
                        </div>
                    @endif

                    <form method="post" action="{{ route('hcm.install') }}" id="install-form">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="license_code" class="form-label">License Code *</label>
                                    <input type="text" 
                                           class="form-control @error('license_code') is-invalid @enderror" 
                                           id="license_code" 
                                           name="license_code" 
                                           value="{{ old('license_code') }}" 
                                           placeholder="Enter your license code"
                                           required>
                                    @error('license_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', auth()->user()->email ?? '') }}" 
                                           placeholder="your@email.com"
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="login_username" class="form-label">Username *</label>
                            <input type="text" 
                                   class="form-control @error('login_username') is-invalid @enderror" 
                                   id="login_username" 
                                   name="login_username" 
                                   value="{{ old('login_username', auth()->user()->username ?? '') }}" 
                                   placeholder="Enter your username"
                                   required>
                            @error('login_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Prerequisites</h6>
                            <ul class="mb-0">
                                <li>Ensure Ultimate POS is properly installed and configured</li>
                                <li>Database backup is recommended before installation</li>
                                <li>Valid HCM API credentials will be required after installation</li>
                                <li>Internet connection is required for API communication</li>
                            </ul>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg" id="install-btn">
                                <span class="btn-text">
                                    <i class="fas fa-download"></i>
                                    Install {{ $module_display_name ?? 'HCM Module' }}
                                </span>
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span class="loading-text d-none">Installing...</span>
                            </button>
                            <a href="{{ url('/dashboard') }}" class="btn btn-secondary btn-lg ml-2">
                                <i class="fas fa-times"></i>
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
                
                <div class="card-footer text-muted">
                    <small>
                        <i class="fas fa-shield-alt"></i>
                        This module is designed specifically for Ultimate Forester POS system.
                        For support, contact: <a href="mailto:support@ultimateforester.com">support@ultimateforester.com</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#install-form').on('submit', function() {
        $('#install-btn').prop('disabled', true);
        $('.btn-text').addClass('d-none');
        $('.spinner-border').removeClass('d-none');
        $('.loading-text').removeClass('d-none');
    });
});
</script>
@endsection
