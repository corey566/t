
@extends('layouts.app')

@section('title', 'HCM Integration Dashboard')

@section('content')
<section class="content-header">
    <h1>HCM Integration Dashboard
        <small>Havelock City Mall Integration</small>
    </h1>
</section>

<section class="content">
    @if(!empty($alerts))
        @foreach($alerts as $type => $message)
            <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-warning"></i> Alert!</h4>
                {{ $message }}
            </div>
        @endforeach
    @endif

    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-cog"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Tenant Config</span>
                    <span class="info-box-number">
                        <a href="{{ action([\Modules\Hcm\Http\Controllers\HcmController::class, 'tenantConfig']) }}" class="btn btn-primary btn-sm">Configure</a>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-refresh"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Sync Invoices</span>
                    <span class="info-box-number">
                        <button type="button" class="btn btn-success btn-sm" id="sync_invoices">Sync Now</button>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Synced Invoices</span>
                    <span class="info-box-number">
                        <a href="{{ action([\Modules\Hcm\Http\Controllers\HcmController::class, 'syncedInvoices']) }}" class="btn btn-warning btn-sm">View</a>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-file-excel-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Reports</span>
                    <span class="info-box-number">
                        <a href="{{ action([\Modules\Hcm\Http\Controllers\HcmController::class, 'reports']) }}" class="btn btn-danger btn-sm">Generate</a>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Connection Test</h3>
                </div>
                <div class="box-body">
                    <form id="test_connection_form">
                        <div class="form-group">
                            <label>Select Location:</label>
                            <select name="location_id" id="location_id" class="form-control" required>
                                <option value="">Select Location</option>
                                @foreach(\App\BusinessLocation::forDropdown(session()->get('business.id')) as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Test Connection</button>
                    </form>
                    <div id="connection_result" class="mt-2"></div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Sync Status</h3>
                </div>
                <div class="box-body">
                    <p><strong>Last Invoice Sync:</strong> <span id="last_invoice_sync">{{ $last_sync ?? 'Never' }}</span></p>
                    <p><strong>Module Version:</strong> {{ config('hcm.module_version') }}</p>
                    <p><strong>API Base URL:</strong> {{ config('hcm.api.base_url') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Quick Actions</h3>
                </div>
                <div class="box-body">
                    <a href="{{ action([\Modules\Hcm\Http\Controllers\HcmController::class, 'pingMonitor']) }}" class="btn btn-info">
                        <i class="fa fa-heartbeat"></i> POS Ping Monitor
                    </a>
                    <a href="{{ action([\Modules\Hcm\Http\Controllers\HcmController::class, 'viewSyncLog']) }}" class="btn btn-default">
                        <i class="fa fa-history"></i> View Sync Log
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    // Test connection
    $('#test_connection_form').on('submit', function(e) {
        e.preventDefault();
        
        var location_id = $('#location_id').val();
        if (!location_id) {
            toastr.error('Please select a location');
            return;
        }

        $.ajax({
            url: '{{ action([\Modules\Hcm\Http\Controllers\HcmController::class, "testConnection"]) }}',
            method: 'GET',
            data: { location_id: location_id },
            success: function(response) {
                if (response.success) {
                    $('#connection_result').html('<div class="alert alert-success">' + response.msg + '</div>');
                } else {
                    $('#connection_result').html('<div class="alert alert-danger">' + response.msg + '</div>');
                }
            },
            error: function() {
                $('#connection_result').html('<div class="alert alert-danger">Connection test failed</div>');
            }
        });
    });

    // Sync invoices
    $('#sync_invoices').on('click', function() {
        $(this).attr('disabled', true).text('Syncing...');
        
        $.ajax({
            url: '{{ action([\Modules\Hcm\Http\Controllers\HcmController::class, "syncInvoices"]) }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    toastr.success('Synced ' + response.synced_count + ' invoices successfully');
                } else {
                    toastr.error(response.msg);
                }
            },
            error: function() {
                toastr.error('Sync failed');
            },
            complete: function() {
                $('#sync_invoices').attr('disabled', false).text('Sync Now');
            }
        });
    });
});
</script>
@endsection
