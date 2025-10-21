
@extends('layouts.app')

@section('title', 'Havelock City Mall Integration')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-building"></i> Havelock City Mall (HCM) API Configuration
                    </h3>
                </div>
                <div class="card-body">
                    <p class="alert alert-info">
                        Configure your HCM API credentials for each business location to enable automatic sales syncing and POS monitoring.
                    </p>

                    @foreach($locations as $location)
                    <div class="card mb-3">
                        <div class="card-header">
                            <h4>{{ $location->name }}</h4>
                        </div>
                        <div class="card-body">
                            <form class="hcm-config-form" data-location-id="{{ $location->id }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>API URL</label>
                                            <input type="text" name="api_url" class="form-control" 
                                                   value="{{ $location->hcmConfig->api_url ?? 'https://trms-api.azurewebsites.net' }}"
                                                   placeholder="https://trms-api.azurewebsites.net">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tenant ID</label>
                                            <input type="text" name="tenant_id" class="form-control" 
                                                   value="{{ $location->hcmConfig->tenant_id ?? '' }}"
                                                   placeholder="Enter Tenant ID">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tenant Secret</label>
                                            <input type="password" name="tenant_secret" class="form-control" 
                                                   value="{{ $location->hcmConfig->tenant_secret ?? '' }}"
                                                   placeholder="Enter Tenant Secret">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>POS ID</label>
                                            <input type="text" name="pos_id" class="form-control" 
                                                   value="{{ $location->hcmConfig->pos_id ?? '' }}"
                                                   placeholder="Enter POS ID">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Stall Number</label>
                                            <input type="text" name="stall_no" class="form-control" 
                                                   value="{{ $location->hcmConfig->stall_no ?? '' }}"
                                                   placeholder="Enter Stall Number">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>
                                                <input type="checkbox" name="is_active" value="1" 
                                                       {{ ($location->hcmConfig->is_active ?? false) ? 'checked' : '' }}>
                                                Active
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="button" class="btn btn-primary save-config">
                                        <i class="fas fa-save"></i> Save Configuration
                                    </button>
                                    <button type="button" class="btn btn-info test-connection">
                                        <i class="fas fa-plug"></i> Test Connection
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.save-config').click(function() {
        var $form = $(this).closest('form');
        var locationId = $form.data('location-id');
        var formData = $form.serialize();

        $.ajax({
            url: '/hcm/save-credentials/' + locationId,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('Failed to save configuration');
            }
        });
    });

    $('.test-connection').click(function() {
        var $form = $(this).closest('form');
        var locationId = $form.data('location-id');

        $.ajax({
            url: '/hcm/location/' + locationId + '/test-connection',
            method: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('Connection test failed');
            }
        });
    });
});
</script>
@endsection
