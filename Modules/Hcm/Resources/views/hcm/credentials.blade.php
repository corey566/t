@extends('layouts.app')

@section('title', 'Havelock City Mall Integration')

@section('content')
<style>
    .gallface-container {
        background-color: #f5f5f5;
        min-height: 100vh;
        padding: 20px;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .stat-card {
        padding: 20px;
        border-radius: 12px;
        color: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .stat-card h3 {
        font-size: 2rem;
        margin: 0 0 5px 0;
        font-weight: bold;
    }

    .stat-card p {
        margin: 0;
        opacity: 0.9;
        font-size: 0.9rem;
    }

    .stat-card.purple {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-card.green {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }

    .stat-card.orange {
        background: linear-gradient(135deg, #fa9352 0%, #ffcc00 100%);
    }

    .stat-card.red {
        background: linear-gradient(135deg, #fc5c7d 0%, #f05454 100%);
    }

    .add-config-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
    }

    .location-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .location-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .location-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.2rem;
        font-weight: 600;
    }

    .status-badge {
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .status-badge.online {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
    }

    .info-banner {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.9rem;
    }

    .form-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }

    .form-control-modern {
        width: 100%;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 10px 15px;
        font-size: 14px;
        transition: all 0.3s ease;
        background: #fff;
    }

    .form-control-modern:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .btn-update {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-test {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-sync {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-ping {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-history {
        background: #6c757d;
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-delete {
        background: linear-gradient(135deg, #fc5c7d 0%, #f05454 100%);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        cursor: pointer;
        margin-top: 10px;
    }

    .checkbox-label input[type="checkbox"] {
        margin-right: 8px;
        width: 18px;
        height: 18px;
    }
</style>

<div class="gallface-container">
    @include('gallface::layouts.nav')

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card purple">
            <h3>{{ $locations->count() }}</h3>
            <p>Business Locations</p>
        </div>
        <div class="stat-card green">
            <h3>{{ $activeIntegrations }}</h3>
            <p>Active Integrations</p>
        </div>
        <div class="stat-card orange">
            <h3>{{ $recentSyncs }}</h3>
            <p>Today's Syncs</p>
        </div>
        <div class="stat-card red">
            <h3>{{ $lastSyncTime }}</h3>
            <p>Last Sync</p>
        </div>
    </div>

    <!-- Add New Configuration Button -->
    <button class="add-config-btn" data-toggle="modal" data-target="#addCredentialModal">
        <i class="fas fa-plus"></i> Add New Configuration
    </button>

    <!-- Location Cards -->
    @foreach($locations as $location)
        @php
            $credential = $location->getCredentialsForMall('hcm');
            $hasCredentials = $credential && $credential->hasCompleteCredentials();
            $isActive = $credential && $credential->is_active;
        @endphp

        <div class="location-card" id="location-{{ $location->id }}">
            <div class="location-header">
                <div class="location-title">
                    <i class="fas fa-map-marker-alt"></i>
                    {{ $location->name }}
                    <small style="color: #999; font-size: 0.8rem;">ID: BL{{ $location->id }}</small>
                </div>
                @if($isActive)
                    <span class="status-badge online">
                        <i class="fas fa-check-circle"></i> Online
                    </span>
                @endif
            </div>

            @if($hasCredentials)
                <div class="info-banner">
                    <i class="fas fa-info-circle"></i>
                    <span>Configuration for <strong>{{ $location->name }}</strong> - Only invoices from this location will sync to HCM API</span>
                </div>

                <form class="credential-save-form">
                    @csrf
                    <input type="hidden" name="location_id" value="{{ $location->id }}">
                    <div class="form-section">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-link"></i> API URL
                                    </label>
                                    <input type="url" name="api_url" class="form-control-modern"
                                        value="{{ $credential->api_url }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-user"></i> Tenant ID
                                    </label>
                                    <input type="text" name="username" class="form-control-modern"
                                        value="{{ $credential->username }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-lock"></i> Tenant Secret
                                    </label>
                                    <input type="password" name="password" class="form-control-modern"
                                        value="{{ $credential->password }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-store"></i> Stall No
                                    </label>
                                    <input type="text" name="stall_no" class="form-control-modern"
                                        value="{{ $credential->stall_no }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-cash-register"></i> POS ID
                                    </label>
                                    <input type="text" name="pos_id" class="form-control-modern"
                                        value="{{ $credential->pos_id }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-sync"></i> Sync Type
                                    </label>
                                    <select name="sync_type" class="form-control-modern">
                                        <option value="manual" {{ $credential->sync_type == 'manual' ? 'selected' : '' }}>Manual</option>
                                        <option value="auto" {{ $credential->sync_type == 'auto' ? 'selected' : '' }}>Auto</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="is_active" {{ $isActive ? 'checked' : '' }}>
                                    <strong>Enable Integration</strong>
                                </label>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="btn-update">
                                <i class="fas fa-save"></i> Update Configuration
                            </button>
                            <button type="button" class="btn-test test-connection" data-location-id="{{ $location->id }}">
                                <i class="fas fa-plug"></i> Test Connection
                            </button>
                            <button type="button" class="btn-sync sync-sales" data-location-id="{{ $location->id }}">
                                <i class="fas fa-sync"></i> Sync Sales Now
                            </button>
                            <button type="button" class="btn-ping send-ping" data-location-id="{{ $location->id }}">
                                <i class="fas fa-heartbeat"></i> Send Ping
                            </button>
                            <a href="{{ url('gallface/hcm/location/' . $location->id . '/invoice-history') }}" class="btn-history">
                                <i class="fas fa-history"></i> View History
                            </a>
                            <a href="{{ url('gallface/hcm/location/' . $location->id . '/ping-monitor') }}" class="btn-history" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <i class="fas fa-terminal"></i> Ping Monitor
                            </a>
                            <button type="button" class="btn-delete delete-credential" data-location-id="{{ $location->id }}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </form>
            @else
                <div style="text-align: center; padding: 40px 20px;">
                    <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                    <p class="text-muted" style="margin-bottom: 15px;">No HCM credentials configured for this location.</p>
                    <button class="add-config-btn configure-location" data-location-id="{{ $location->id }}" data-location-name="{{ $location->name }}">
                        <i class="fas fa-plus"></i> Configure Integration
                    </button>
                </div>
            @endif
        </div>
    @endforeach
</div>

<!-- Add Credential Modal -->
<div class="modal fade" id="addCredentialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Add HCM Configuration</h5>
                <button type="button" class="close" data-dismiss="modal" style="color: white;">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ url('gallface/hcm/save-credentials/0') }}" method="POST" id="addCredentialForm">
                @csrf
                <div class="modal-body" style="padding: 25px;">
                    <div class="form-group">
                        <label class="form-label">Select Location *</label>
                        <select name="business_location_id" class="form-control-modern" required>
                            <option value="">-- Select Location --</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">API URL *</label>
                                <input type="url" name="api_url" class="form-control-modern"
                                    value="https://trms-api.azurewebsites.net" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Tenant ID *</label>
                                <input type="text" name="username" class="form-control-modern" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Tenant Secret *</label>
                                <input type="password" name="password" class="form-control-modern" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Stall No *</label>
                                <input type="text" name="stall_no" class="form-control-modern" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">POS ID *</label>
                                <input type="text" name="pos_id" class="form-control-modern" required>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" checked>
                            <strong>Enable Integration</strong>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-update">
                        <i class="fas fa-save"></i> Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('.configure-location').click(function() {
        var locationId = $(this).data('location-id');
        var locationName = $(this).data('location-name');
        $('#addCredentialModal').modal('show');
        $('select[name="business_location_id"]').val(locationId);
        $('.modal-title').html('<i class="fas fa-plus-circle"></i> Configure HCM for ' + locationName);
        $('#addCredentialForm').attr('action', '/gallface/hcm/save-credentials/' + locationId);
    });

    $('.test-connection, .sync-sales, .send-ping').click(function(e) {
        e.preventDefault();
        var locationId = $(this).data('location-id');
        var btn = $(this);
        var originalText = btn.html();
        var url = '';

        if (btn.hasClass('send-ping')) {
            url = '/gallface/hcm/location/' + locationId + '/ping';
        } else if (btn.hasClass('test-connection')) {
            url = '/gallface/hcm/location/' + locationId + '/test-connection';
        } else if (btn.hasClass('sync-sales')) {
            url = '/gallface/hcm/location/' + locationId + '/sync-sales';
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

        $.ajax({
            url: url,
            type: 'POST',
            success: function(response) {
                btn.prop('disabled', false).html(originalText);
                if(response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Operation completed successfully',
                        timer: 3000
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Notice',
                        text: response.message || 'Operation completed with warnings'
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Operation failed'
                });
            }
        });
    });

    $('.delete-credential').click(function() {
        var locationId = $(this).data('location-id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the HCM configuration for this location!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("gallface/hcm/delete-credentials") }}/' + locationId,
                    type: 'DELETE',
                    success: function(response) {
                        if(response.success) {
                            Swal.fire('Deleted!', response.message, 'success').then(() => location.reload());
                        }
                    }
                });
            }
        });
    });

    $('.credential-save-form, #addCredentialForm').submit(function(e) {
        e.preventDefault();
        var form = $(this);
        var locationId = form.find('input[name="location_id"]').val() || $('select[name="business_location_id"]').val();

        $.ajax({
            url: '{{ url("gallface/hcm/save-credentials") }}/' + locationId,
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000
                    }).then(() => location.reload());
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to save', 'error');
            }
        });
    });
});
</script>
@endsection