
@extends('layouts.app')

@section('title', 'HCM Tenant Configuration')

@section('content')
<section class="content-header">
    <h1>HCM Tenant Configuration
        <small>Configure tenant settings for each location</small>
    </h1>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Tenant Settings</h3>
        </div>
        
        <form method="POST" action="{{ action([\Modules\Hcm\Http\Controllers\HcmController::class, 'updateTenantConfig']) }}">
            @csrf
            <div class="box-body">
                @foreach($locations as $location_id => $location_name)
                    @php
                        $config = $configs->where('location_id', $location_id)->first();
                    @endphp
                    
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">{{ $location_name }}</h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tenant ID *</label>
                                        <input type="text" name="configs[{{ $location_id }}][tenant_id]" 
                                               class="form-control" 
                                               value="{{ $config->tenant_id ?? '' }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tenant Secret *</label>
                                        <input type="password" name="configs[{{ $location_id }}][tenant_secret]" 
                                               class="form-control" 
                                               value="{{ $config->tenant_secret ?? '' }}" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>POS ID *</label>
                                        <input type="text" name="configs[{{ $location_id }}][pos_id]" 
                                               class="form-control" 
                                               value="{{ $config->pos_id ?? '' }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Stall Number</label>
                                        <input type="text" name="configs[{{ $location_id }}][stall_no]" 
                                               class="form-control" 
                                               value="{{ $config->stall_no ?? '' }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>API URL</label>
                                        <input type="url" name="configs[{{ $location_id }}][api_url]" 
                                               class="form-control" 
                                               value="{{ $config->api_url ?? config('hcm.api.base_url') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Retry Attempts</label>
                                        <input type="number" name="configs[{{ $location_id }}][retry_attempts]" 
                                               class="form-control" min="1" max="10"
                                               value="{{ $config->retry_attempts ?? 3 }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="configs[{{ $location_id }}][active]" 
                                                   value="1" {{ ($config && $config->active) ? 'checked' : '' }}>
                                            Active
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="configs[{{ $location_id }}][auto_sync]" 
                                                   value="1" {{ ($config && $config->auto_sync) ? 'checked' : '' }}>
                                            Auto Sync
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Save Configuration</button>
                <a href="{{ action([\Modules\Hcm\Http\Controllers\HcmController::class, 'index']) }}" class="btn btn-default">Back</a>
            </div>
        </form>
    </div>

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">Configuration Instructions</h3>
        </div>
        <div class="box-body">
            <ol>
                <li><strong>Tenant ID:</strong> Your unique tenant identifier provided by HCM</li>
                <li><strong>Tenant Secret:</strong> Your secret key for authentication</li>
                <li><strong>POS ID:</strong> Your POS terminal identifier</li>
                <li><strong>Stall Number:</strong> Your stall number in the mall (optional)</li>
                <li><strong>API URL:</strong> HCM API endpoint (default: {{ config('hcm.api.base_url') }})</li>
                <li><strong>Active:</strong> Enable/disable sync for this location</li>
                <li><strong>Auto Sync:</strong> Automatically sync invoices in background</li>
                <li><strong>Retry Attempts:</strong> Number of retry attempts for failed syncs</li>
            </ol>
            
            <div class="alert alert-info">
                <h4>Test Credentials (for testing only):</h4>
                <ul>
                    <li>Tenant ID: {{ config('hcm.api.test_credentials.tenant_id') }}</li>
                    <li>Tenant Secret: {{ config('hcm.api.test_credentials.tenant_secret') }}</li>
                    <li>POS ID: {{ config('hcm.api.test_credentials.pos_id') }}</li>
                </ul>
            </div>
        </div>
    </div>
</section>
@endsection
