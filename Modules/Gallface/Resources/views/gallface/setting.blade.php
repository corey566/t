@extends('layouts.app')

@section('title', 'One Gallface Mall Integration')

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
    <button class="add-config-btn" data-toggle="modal" data-target="#addConfigModal">
        <i class="fas fa-plus"></i> Add New Configuration
    </button>

    <!-- Location Cards -->
    @foreach($locations as $location)
        @php
            // Get first gallface credential for this location
            $credential = $location->credentials->where('mall_code', 'gallface')->first();

            $hasCredentials = $credential && !empty($credential->client_id) && !empty($credential->client_secret);
            $isActive = $credential && $credential->is_active;
            $additionalData = $credential ? json_decode($credential->additional_data ?? '{}', true) : [];

            // Debug output
            if ($credential) {
                \Log::info('Displaying credential for location: ' . $location->name, [
                    'credential_id' => $credential->id,
                    'client_id' => $credential->client_id,
                    'has_additional_data' => !empty($credential->additional_data),
                    'additional_data' => $additionalData
                ]);
            }
        @endphp

        <div class="location-card" id="location-{{ $location->id }}">
            <div class="location-header">
                <div class="location-title">
                    <i class="fas fa-map-marker-alt"></i>
                    {{ $location->name }}
                    <small style="color: #999; font-size: 0.8rem;">ID: {{ $location->location_id ?? 'N/A' }}</small>
                </div>
                @if($isActive)
                    <span class="status-badge online">
                        <i class="fas fa-check-circle"></i> Online
                    </span>
                @endif
            </div>

            @if($hasCredentials)
                <form class="api-update-form" data-credential-id="{{ $credential->id }}">
                    @csrf
                    <div class="form-section">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-key"></i> Token URL *
                                </label>
                                <input type="url" name="access_token_url" class="form-control-modern"
                                    value="{{ $credential->api_url }}" required
                                    placeholder="https://mims.imonitor.center/connect/token">
                                <small class="text-muted">OAuth2 token endpoint</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-server"></i> Production URL *
                                </label>
                                <input type="url" name="production_url" class="form-control-modern"
                                    value="{{ $additionalData['production_url'] ?? 'https://mims.imonitor.center' }}" required
                                    placeholder="https://mims.imonitor.center">
                                <small class="text-muted">MIMS API base URL</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user"></i> Client ID *
                                </label>
                                <input type="text" name="client_id" class="form-control-modern"
                                    value="{{ $credential->client_id }}" required
                                    placeholder="CCB1-PS-19-00000216">
                                <small class="text-muted">Provided by One Galle Face</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i> Client Secret *
                                </label>
                                <input type="password" name="client_secret" class="form-control-modern"
                                    value="{{ $credential->client_secret }}" required
                                    placeholder="Enter client secret">
                                <small class="text-muted">Provided by One Galle Face</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-building"></i> Property Code *
                                </label>
                                <input type="text" name="property_code" class="form-control-modern"
                                    value="{{ $additionalData['property_code'] ?? 'CCB1' }}" required
                                    placeholder="CCB1">
                                <small class="text-muted">Mall property identifier</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-cash-register"></i> POS Interface Code *
                                </label>
                                <input type="text" name="pos_interface_code" class="form-control-modern"
                                    value="{{ $additionalData['pos_interface_code'] ?? $credential->client_id }}" required
                                    placeholder="CCB1-PS-19-00000216">
                                <small class="text-muted">Usually same as Client ID</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-code"></i> App Code
                                </label>
                                <input type="text" name="app_code" class="form-control-modern"
                                    value="{{ $additionalData['app_code'] ?? 'POS-02' }}"
                                    placeholder="POS-02">
                                <small class="text-muted">Application identifier (default: POS-02)</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_active" {{ $isActive ? 'checked' : '' }}>
                                <strong>Enable Integration</strong>
                            </label>
                        </div>
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
                        <a href="{{ url('gallface/location/' . $location->id . '/invoice-history') }}" class="btn-history">
                            <i class="fas fa-history"></i> View History
                        </a>
                        <button type="button" class="btn-delete delete-credential" data-id="{{ $credential->id }}">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </form>
            @else
                <div style="text-align: center; padding: 40px 20px;">
                    <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                    <p class="text-muted" style="margin-bottom: 15px;">No configuration found for this location.</p>
                    <button class="add-config-btn" data-location-id="{{ $location->id }}" data-location-name="{{ $location->name }}">
                        <i class="fas fa-plus"></i> Configure Integration
                    </button>
                </div>
            @endif
        </div>
    @endforeach
</div>

<!-- Add Configuration Modal -->
<div class="modal fade" id="addConfigModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Add Gallface Configuration</h5>
                <button type="button" class="close" data-dismiss="modal" style="color: white;">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addConfigForm">
                @csrf
                <div class="modal-body" style="padding: 25px;">
                    <div class="form-group">
                        <label class="form-label">Select Location *</label>
                        <select name="business_location_id" class="form-control-modern" required>
                            <option value="">-- Select Location --</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-key"></i> Token URL *
                                </label>
                                <input type="url" name="access_token_url" class="form-control-modern" required
                                    placeholder="https://mims.imonitor.center/connect/token">
                                <small class="text-muted">OAuth2 token endpoint</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-server"></i> Production URL *
                                </label>
                                <input type="url" name="production_url" class="form-control-modern" required
                                    placeholder="https://mims.imonitor.center">
                                <small class="text-muted">MIMS API base URL</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user"></i> Client ID *
                                </label>
                                <input type="text" name="client_id" class="form-control-modern" required
                                    placeholder="CCB1-PS-19-00000216">
                                <small class="text-muted">Provided by One Galle Face</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i> Client Secret *
                                </label>
                                <input type="password" name="client_secret" class="form-control-modern" required
                                    placeholder="Enter client secret">
                                <small class="text-muted">Provided by One Galle Face</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-building"></i> Property Code *
                                </label>
                                <input type="text" name="property_code" class="form-control-modern" required
                                    placeholder="CCB1">
                                <small class="text-muted">Mall property identifier</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-cash-register"></i> POS Interface Code *
                                </label>
                                <input type="text" name="pos_interface_code" class="form-control-modern" required
                                    placeholder="CCB1-PS-19-00000216">
                                <small class="text-muted">Usually same as Client ID</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-code"></i> App Code
                                </label>
                                <input type="text" name="app_code" class="form-control-modern"
                                    value="{{ $additionalData['app_code'] ?? 'POS-02' }}"
                                    placeholder="POS-02">
                                <small class="text-muted">Application identifier (default: POS-02)</small>
                            </div>
                        </div>
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

    $('.test-connection, .sync-sales, .send-ping').click(function(e) {
        e.preventDefault();
        var locationId = $(this).data('location-id');
        var btn = $(this);
        var originalText = btn.html();
        var action = btn.hasClass('test-connection') ? 'test' : (btn.hasClass('sync-sales') ? 'sync' : 'ping');

        var url = '';
        if (btn.hasClass('send-ping')) {
            url = '/gallface/gallface/location/' + locationId + '/ping';
        } else if (btn.hasClass('test-connection')) {
            url = '/gallface/gallface/location/' + locationId + '/test-connection';
        } else if (btn.hasClass('sync-sales')) {
            url = '/gallface/gallface/location/' + locationId + '/sync-sales';
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

        $.ajax({
            url: url,
            method: 'POST',
            success: function(response) {
                btn.prop('disabled', false).html(originalText);
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        html: response.message + (response.records_synced ? '<br><small>Records synced: ' + response.records_synced + '</small>' : ''),
                        timer: 3000
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sync Failed',
                        html: '<div style="text-align: left;">' + (response.message || 'Unknown error occurred') + '</div>',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalText);
                
                let errorMsg = 'Request failed';
                if (xhr.responseJSON?.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const parsed = JSON.parse(xhr.responseText);
                        errorMsg = parsed.message || errorMsg;
                    } catch(e) {
                        errorMsg = xhr.responseText.substring(0, 200);
                    }
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: '<div style="text-align: left;">' + errorMsg + '</div>',
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    $('.delete-credential').click(function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the configuration!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("gallface/api/delete") }}/' + id,
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

    $('.api-update-form').submit(function(e) {
        e.preventDefault();
        var form = $(this);
        var credentialId = form.data('credential-id');

        // Extract additional data fields from the form
        var formData = form.serializeArray();
        var additionalData = {};
        $.each(formData, function(i, field){
            if (field.name === 'access_token_url') {
                // Update the main api_url field if it exists
                form.find('input[name="api_url"]').val(field.value);
            } else if (field.name !== 'is_active') { // Exclude is_active from additional_data
                additionalData[field.name] = field.value;
            }
        });

        // Add the additional_data JSON to the form data
        formData.push({name: 'additional_data', value: JSON.stringify(additionalData)});

        // Also handle the checkbox separately
        var is_active = form.find('input[name="is_active"]').is(':checked') ? 1 : 0;
        formData.push({name: 'is_active', value: is_active});


        $.ajax({
            url: '{{ url("gallface/api/update") }}/' + credentialId,
            type: 'POST',
            data: formData, // Use the modified formData
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000
                    }).then(() => location.reload());
                } else {
                     Swal.fire('Error', response.message || 'Failed to save', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to save', 'error');
            }
        });
    });

    // Logic for the "Configure Integration" button in the location card
    $('.location-card .add-config-btn').click(function() {
        var locationId = $(this).data('location-id');
        var locationName = $(this).data('location-name');
        $('#addConfigModal select[name="business_location_id"]').val(locationId).trigger('change');
        $('#addConfigModal .modal-title').html('<i class="fas fa-plus-circle"></i> Configure Gallface for ' + locationName);
        $('#addConfigModal').modal('show');
    });

    // Submit handler for the add configuration modal
    $('#addConfigForm').submit(function(e) {
        e.preventDefault();
        var form = $(this);

        $.ajax({
            url: '{{ url("gallface/api/save") }}', // Assuming this is the correct endpoint
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Configuration saved successfully',
                        timer: 2000
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message || 'Failed to save configuration', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to save configuration', 'error');
            }
        });
    });
});
</script>
@endsection