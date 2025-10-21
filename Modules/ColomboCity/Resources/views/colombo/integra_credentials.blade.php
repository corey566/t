
@extends('layouts.app')

@section('title', 'Colombo City Center (Integra)')

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

    .integration-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }

    .settings-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .form-control-modern {
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 10px 15px;
        font-size: 14px;
        transition: all 0.3s ease;
        width: 100%;
        background: #fff;
    }

    .form-control-modern:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .btn-primary-modern {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .info-banner {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .credentials-info {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e0e0e0;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .copy-btn {
        background: #667eea;
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        margin-left: 10px;
    }

    .filter-controls {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .filter-group {
        flex: 1;
        min-width: 200px;
    }

    .filter-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        font-size: 14px;
    }

    .btn-export {
        padding: 8px 16px;
        border: 1px solid #ddd;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
    }
</style>

<div class="gallface-container">
    @include('gallface::layouts.nav')

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card purple">
            <h3>1</h3>
            <p>Business Locations</p>
        </div>
        <div class="stat-card green">
            <h3>1</h3>
            <p>Active Integrations</p>
        </div>
        <div class="stat-card orange">
            <h3>0</h3>
            <p>Today's Syncs</p>
        </div>
        <div class="stat-card red">
            <h3>Never</h3>
            <p>Last Sync</p>
        </div>
    </div>

    <!-- Settings Section -->
    <div class="integration-card">
        <h3 style="margin-bottom: 20px;">
            <i class="fas fa-cog"></i> API Configuration
        </h3>

        <form action="{{ url('gallface/integra/save-credentials') }}" method="POST" id="integraCredentialsForm">
            @csrf

            <div class="info-banner" style="font-size: 13px;">
                <i class="fas fa-info-circle"></i>
                <span>These credentials will be used by Colombo City Center (Integra POS) to authenticate when sending sales data to your system.</span>
            </div>

            <div class="settings-section">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label style="font-weight: 600; margin-bottom: 8px; display: block;">
                                <i class="fas fa-user"></i> API Username *
                            </label>
                            <input type="text" name="username" class="form-control-modern" 
                                value="{{ env('INTEGRA_API_USER') }}" required
                                placeholder="Enter username for API authentication">
                            <small class="form-text text-muted">Provide this username to Colombo City Center team</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label style="font-weight: 600; margin-bottom: 8px; display: block;">
                                <i class="fas fa-lock"></i> API Password *
                            </label>
                            <input type="password" name="password" class="form-control-modern" 
                                value="{{ env('INTEGRA_API_PASS') }}" required
                                placeholder="Enter password for API authentication">
                            <small class="form-text text-muted">Provide this password to Colombo City Center team</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label style="font-weight: 600; margin-bottom: 8px; display: block;">
                                <i class="fas fa-map-marker-alt"></i> Location Code
                            </label>
                            <input type="text" name="location_code" class="form-control-modern" 
                                value="{{ env('INTEGRA_LOCATION_CODE', '01') }}" 
                                placeholder="Enter location code (default: 01)">
                            <small class="form-text text-muted">Branch/POS ID of the store</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label style="font-weight: 600; margin-bottom: 8px; display: block;">
                                <i class="fas fa-desktop"></i> Terminal ID
                            </label>
                            <input type="text" name="terminal_id" class="form-control-modern" 
                                value="{{ env('INTEGRA_TERMINAL_ID', '01') }}" 
                                placeholder="Enter terminal ID (default: 01)">
                            <small class="form-text text-muted">Terminal ID of the POS till</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn-primary-modern">
                            <i class="fas fa-save"></i> Update Configuration
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="credentials-info">
            <h4 style="margin-bottom: 15px;">
                <i class="fas fa-info-circle"></i> API Endpoint Information
            </h4>

            <div class="info-row">
                <span style="font-weight: 600;">API Endpoint URL:</span>
                <span style="font-family: monospace;">
                    {{ url('/api/gallface/integra/receive') }}
                    <button class="copy-btn" onclick="copyToClipboard('{{ url('/api/gallface/integra/receive') }}')">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </span>
            </div>

            <div class="info-row">
                <span style="font-weight: 600;">HTTP Method:</span>
                <span>POST</span>
            </div>

            <div class="info-row">
                <span style="font-weight: 600;">Authentication Type:</span>
                <span>Basic Authentication</span>
            </div>

            <div class="info-row">
                <span style="font-weight: 600;">Supported Formats:</span>
                <span>JSON, XML</span>
            </div>
        </div>
    </div>

    <!-- Sync Logs Section -->
    <div class="integration-card">
        <h3 style="margin-bottom: 20px;">
            <i class="fas fa-history"></i> Sync Logs
        </h3>

        <div class="filter-controls">
            <div class="filter-group">
                <label>Date From</label>
                <input type="date" id="date-from" class="form-control-modern" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
            </div>
            <div class="filter-group">
                <label>Date To</label>
                <input type="date" id="date-to" class="form-control-modern" value="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select id="status-filter" class="form-control-modern">
                    <option value="">All Status</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <div class="filter-group" style="flex: 0; display: flex; align-items: flex-end;">
                <button type="button" id="applyFilters" class="btn-primary-modern">
                    <i class="fas fa-filter"></i> Apply
                </button>
            </div>
        </div>

        <div style="margin-bottom: 15px; display: flex; justify-content: flex-end; gap: 10px;">
            <button class="btn-export" id="exportCSV"><i class="fas fa-file-csv"></i> Export CSV</button>
            <button class="btn-export" id="printTable"><i class="fas fa-print"></i> Print</button>
        </div>

        <div style="overflow-x: auto;">
            <table class="table table-striped" id="syncTable">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>IP Address</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Message</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #999;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px; display: flex; justify-content: space-between;">
            <div id="showingInfo" style="color: #666;">Showing 0 to 0 of 0 entries</div>
            <div id="pagination"></div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let logsData = [];

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'API endpoint copied to clipboard',
            timer: 2000,
            showConfirmButton: false
        });
    });
}

$(document).ready(function() {
    $('#integraCredentialsForm').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'Credentials saved successfully',
                    timer: 2000
                }).then(() => location.reload());
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to save credentials'
                });
            }
        });
    });

    loadLogs();

    $('#applyFilters').click(function() {
        loadLogs();
    });

    $('#exportCSV').click(function() {
        exportToCSV();
    });

    $('#printTable').click(function() {
        window.print();
    });
});

function loadLogs() {
    const dateFrom = $('#date-from').val();
    const dateTo = $('#date-to').val();
    const status = $('#status-filter').val();

    $('#tableBody').html('<tr><td colspan="6" style="text-align: center; padding: 40px; color: #999;">Loading...</td></tr>');

    $.ajax({
        url: '{{ url("gallface/integra/api-logs") }}',
        type: 'GET',
        data: {
            date_from: dateFrom,
            date_to: dateTo,
            status: status
        },
        success: function(response) {
            if (response.success) {
                logsData = response.data;
                renderTable();
            }
        }
    });
}

function renderTable() {
    if (!logsData || logsData.length === 0) {
        $('#tableBody').html('<tr><td colspan="6" style="text-align: center; padding: 40px; color: #999;">No logs found</td></tr>');
        return;
    }

    let html = '';
    logsData.forEach(log => {
        const statusBadge = log.status === 'success' 
            ? '<span class="badge badge-success">Success</span>'
            : '<span class="badge badge-danger">Failed</span>';

        html += `<tr>
            <td>${log.created_at || '-'}</td>
            <td>${log.ip_address || '-'}</td>
            <td><strong>${log.request_method || '-'}</strong></td>
            <td>${statusBadge}</td>
            <td>${log.message || '-'}</td>
            <td>${log.duration_ms || '-'} ms</td>
        </tr>`;
    });

    $('#tableBody').html(html);
    $('#showingInfo').text(`Showing 1 to ${logsData.length} of ${logsData.length} entries`);
}

function exportToCSV() {
    if (!logsData || logsData.length === 0) {
        Swal.fire('No Data', 'There is no data to export', 'info');
        return;
    }

    let csv = 'Timestamp,IP Address,Method,Status,Message,Duration\n';
    logsData.forEach(log => {
        csv += `"${log.created_at || ''}","${log.ip_address || ''}","${log.request_method || ''}","${log.status || ''}","${log.message || ''}","${log.duration_ms || ''} ms"\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'integra_logs_' + new Date().getTime() + '.csv';
    a.click();
}
</script>
@endsection
