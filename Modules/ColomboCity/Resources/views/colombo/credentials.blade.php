
@extends('layouts.app')

@section('title', __('Colombo City Center'))

@section('content')
<style>
    .gallface-container {
        background-color: #f5f5f5;
        min-height: 100vh;
        padding: 20px;
    }

    .gallface-header {
        background: white;
        border-radius: 12px;
        padding: 20px 30px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    }

    .gallface-tabs {
        display: flex;
        gap: 0;
        margin-top: 15px;
        border-bottom: 2px solid #e0e0e0;
    }

    .gallface-tab {
        padding: 12px 30px;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        font-weight: 500;
        color: #666;
        transition: all 0.3s ease;
        background: transparent;
        border: none;
        outline: none;
        font-size: 14px;
        position: relative;
        z-index: 10;
    }

    .gallface-tab.active {
        color: #667eea;
        border-bottom-color: #667eea;
    }

    .gallface-tab:hover {
        color: #667eea;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .api-config-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }

    .sync-log-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .location-select-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .credential-form {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-top: 15px;
    }

    .form-control-modern {
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 10px 15px;
        font-size: 14px;
        transition: all 0.3s ease;
        width: 100%;
    }

    .form-control-modern:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .btn-add-api {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-add-api:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .btn-update {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
        border: none;
        padding: 10px 30px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-update:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(67, 233, 123, 0.4);
    }

    .btn-delete {
        background: #dc3545;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
    }

    .btn-delete:hover {
        background: #c82333;
    }

    .sync-log-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .show-entries {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .export-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-export {
        padding: 8px 16px;
        border: 1px solid #ddd;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .btn-export:hover {
        background: #f8f9fa;
        border-color: #667eea;
    }

    .sync-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        overflow-x: auto;
    }

    .sync-table th {
        background: #f8f9fa;
        padding: 15px 12px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #e0e0e0;
        cursor: pointer;
        white-space: nowrap;
    }

    .sync-table th:hover {
        background: #e9ecef;
    }

    .sync-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #e0e0e0;
    }

    .sync-table tr:hover {
        background: #f8f9fa;
    }

    .no-data {
        text-align: center;
        padding: 40px;
        color: #999;
    }

    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .status-badge.success {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
    }

    .status-badge.failed {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: white;
    }

    .status-badge.pending {
        background: linear-gradient(135deg, #fa9352 0%, #ffcc00 100%);
        color: white;
    }

    .filters-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .filter-form {
        display: flex;
        gap: 15px;
        align-items: flex-end;
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

    .btn-filter {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }

    .api-connection-item {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 15px;
        border: 2px solid #e0e0e0;
        transition: all 0.3s ease;
    }

    .api-connection-item:hover {
        border-color: #667eea;
    }

    .connection-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .connection-title {
        font-weight: 600;
        font-size: 16px;
        color: #333;
    }
</style>

<div class="gallface-container">
    @include('gallface::layouts.nav')

    <div class="gallface-header">
        <h1 style="font-size: 24px; font-weight: 700; margin: 0;">Colombo City Center Integration</h1>
        <div class="gallface-tabs">
            <button class="gallface-tab active" data-tab="sync-log">Sync Log</button>
            <button class="gallface-tab" data-tab="settings">Settings</button>
        </div>
    </div>

    <!-- Sync Log Tab -->
    <div class="tab-content active" id="sync-log">
        <div class="sync-log-card">
            <!-- Filters -->
            <div class="filters-section">
                <form id="filterForm" class="filter-form">
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
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div class="filter-group" style="flex: 0;">
                        <button type="button" id="applyFilters" class="btn-filter">
                            <i class="fas fa-filter"></i> Apply
                        </button>
                    </div>
                </form>
            </div>

            <div class="sync-log-controls">
                <div class="show-entries">
                    <span>Show</span>
                    <select id="perPage" class="form-control-modern" style="width: 80px;">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>entries</span>
                </div>
                <div class="export-buttons">
                    <button class="btn-export" id="exportCSV"><i class="fas fa-file-csv"></i> Export CSV</button>
                    <button class="btn-export" id="printTable"><i class="fas fa-print"></i> Print</button>
                </div>
                <div>
                    <input type="text" id="searchInput" class="form-control-modern" placeholder="Search..." style="width: 250px;">
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="sync-table" id="syncTable">
                    <thead>
                        <tr>
                            <th data-sort="created_at">Date <i class="fas fa-sort"></i></th>
                            <th data-sort="request_method">Method <i class="fas fa-sort"></i></th>
                            <th data-sort="endpoint">Endpoint <i class="fas fa-sort"></i></th>
                            <th data-sort="status">Status <i class="fas fa-sort"></i></th>
                            <th data-sort="duration_ms">Duration <i class="fas fa-sort"></i></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td colspan="6" class="no-data">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                <div id="showingInfo" style="color: #666;">Showing 0 to 0 of 0 entries</div>
                <div id="pagination" style="display: flex; gap: 10px;"></div>
            </div>
        </div>
    </div>

    <!-- Settings Tab -->
    <div class="tab-content" id="settings">
        <div class="api-config-card">
            <h3 style="margin-top: 0;">API Configuration</h3>

            <div class="location-select-section">
                <div class="form-group">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">
                        <i class="fas fa-map-marker-alt"></i> Select Business Location
                    </label>
                    <select id="location-filter" class="form-control-modern">
                        <option value="">All Locations</option>
                        @foreach(\App\BusinessLocation::where('business_id', session()->get('user.business_id'))->where('is_active', true)->get() as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Configure API endpoint for Colombo City Center integration</small>
                </div>
            </div>

            <div style="text-align: right; margin-bottom: 20px;">
                <button class="btn-add-api" onclick="showApiInfo()">
                    <i class="fas fa-info-circle"></i> View API Endpoint Info
                </button>
            </div>

            <div class="alert alert-info">
                <h5><i class="icon fas fa-info-circle"></i> API Endpoint URL:</h5>
                <code id="api-endpoint">{{ url('/api/gallface/colombo-city/sales-data') }}</code>
                <button class="btn btn-sm btn-primary ml-2" onclick="copyToClipboard()">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('javascript')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentPage = 1;
let logsData = [];
let filteredData = [];
let sortColumn = '';
let sortDirection = 'asc';

// Tab switching
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.gallface-tab').forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const targetTab = this.getAttribute('data-tab');

            document.querySelectorAll('.gallface-tab').forEach(function(t) {
                t.classList.remove('active');
            });
            this.classList.add('active');

            document.querySelectorAll('.tab-content').forEach(function(content) {
                content.classList.remove('active');
            });

            const targetContent = document.getElementById(targetTab);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });

    loadSyncLogs();
});

$(document).ready(function() {
    $('#applyFilters').click(function() {
        currentPage = 1;
        loadSyncLogs();
    });

    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            applyFiltersAndSearch();
        }, 300);
    });

    $('#perPage').change(function() {
        currentPage = 1;
        renderTable();
    });

    $(document).on('click', '.sync-table th[data-sort]', function() {
        const column = $(this).data('sort');
        if (sortColumn === column) {
            sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            sortColumn = column;
            sortDirection = 'asc';
        }

        $('.sync-table th i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
        const icon = $(this).find('i');
        icon.removeClass('fa-sort').addClass(sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down');

        applyFiltersAndSearch();
    });

    $('#exportCSV').click(function() {
        exportToCSV();
    });

    $('#printTable').click(function() {
        printTable();
    });
});

function loadSyncLogs() {
    const dateFrom = $('#date-from').val();
    const dateTo = $('#date-to').val();
    const status = $('#status-filter').val();

    $('#tableBody').html('<tr><td colspan="6" class="no-data">Loading...</td></tr>');

    $.ajax({
        url: '{{ url("gallface/colombo/sync-logs") }}',
        type: 'GET',
        data: {
            date_from: dateFrom,
            date_to: dateTo,
            status: status
        },
        success: function(response) {
            if (response.success) {
                logsData = response.data;
                applyFiltersAndSearch();
            } else {
                $('#tableBody').html('<tr><td colspan="6" class="no-data">Failed to load data</td></tr>');
            }
        },
        error: function() {
            $('#tableBody').html('<tr><td colspan="6" class="no-data">Error loading data</td></tr>');
        }
    });
}

function applyFiltersAndSearch() {
    const searchTerm = $('#searchInput').val().toLowerCase();

    filteredData = logsData.filter(log => {
        const searchMatch = !searchTerm || 
            (log.endpoint && log.endpoint.toLowerCase().includes(searchTerm)) ||
            (log.request_method && log.request_method.toLowerCase().includes(searchTerm)) ||
            (log.status && log.status.toLowerCase().includes(searchTerm));

        return searchMatch;
    });

    if (sortColumn) {
        filteredData.sort((a, b) => {
            let aVal = a[sortColumn] || '';
            let bVal = b[sortColumn] || '';

            if (sortColumn === 'duration_ms') {
                aVal = parseFloat(aVal) || 0;
                bVal = parseFloat(bVal) || 0;
            }

            if (sortDirection === 'asc') {
                return aVal > bVal ? 1 : -1;
            } else {
                return aVal < bVal ? 1 : -1;
            }
        });
    }

    renderTable();
}

function renderTable() {
    const perPage = parseInt($('#perPage').val()) || 25;
    const start = (currentPage - 1) * perPage;
    const end = start + perPage;
    const pageData = filteredData.slice(start, end);

    if (!filteredData || filteredData.length === 0) {
        $('#tableBody').html('<tr><td colspan="6" class="no-data"><i class="fas fa-inbox fa-3x text-muted" style="display: block; margin-bottom: 15px;"></i><p class="text-muted">No sync logs found.</p></td></tr>');
        $('#showingInfo').text('Showing 0 to 0 of 0 entries');
        $('#pagination').html('');
        return;
    }

    let html = '';
    pageData.forEach(log => {
        const statusBadge = log.status === 'success' 
            ? '<span class="status-badge success"><i class="fas fa-check-circle"></i> Success</span>'
            : log.status === 'failed'
            ? '<span class="status-badge failed"><i class="fas fa-times-circle"></i> Failed</span>'
            : '<span class="status-badge pending"><i class="fas fa-clock"></i> Pending</span>';

        const createdAt = log.created_at ? new Date(log.created_at).toLocaleString() : '-';
        const duration = log.duration_ms ? log.duration_ms + ' ms' : '-';

        html += `<tr>
            <td>${createdAt}</td>
            <td><strong>${log.request_method || '-'}</strong></td>
            <td>${log.endpoint || '-'}</td>
            <td>${statusBadge}</td>
            <td>${duration}</td>
            <td>
                <button class="btn btn-sm btn-info" onclick="viewLogDetails(${log.id})">
                    <i class="fas fa-eye"></i> View
                </button>
            </td>
        </tr>`;
    });

    $('#tableBody').html(html);

    const showing = `Showing ${start + 1} to ${Math.min(end, filteredData.length)} of ${filteredData.length} entries`;
    $('#showingInfo').text(showing);

    renderPagination(perPage);
}

function renderPagination(perPage) {
    const totalPages = Math.ceil(filteredData.length / perPage);
    let html = '';

    html += `<button class="btn-export" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})">Previous</button>`;

    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `<button class="btn-export ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += `<span style="padding: 0 10px;">...</span>`;
        }
    }

    html += `<button class="btn-export" ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})">Next</button>`;

    $('#pagination').html(html);
}

function changePage(page) {
    const perPage = parseInt($('#perPage').val());
    const totalPages = Math.ceil(filteredData.length / perPage);

    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        renderTable();
    }
}

function viewLogDetails(logId) {
    const log = logsData.find(l => l.id === logId);
    if (!log) return;

    Swal.fire({
        title: 'Sync Log Details',
        html: `
            <div style="text-align: left;">
                <p><strong>Method:</strong> ${log.request_method}</p>
                <p><strong>Endpoint:</strong> ${log.endpoint}</p>
                <p><strong>Status:</strong> ${log.status}</p>
                <p><strong>Duration:</strong> ${log.duration_ms} ms</p>
                <p><strong>Request Data:</strong></p>
                <pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 200px; overflow-y: auto;">${JSON.stringify(JSON.parse(log.request_data || '{}'), null, 2)}</pre>
                <p><strong>Response Data:</strong></p>
                <pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 200px; overflow-y: auto;">${JSON.stringify(JSON.parse(log.response_data || '{}'), null, 2)}</pre>
            </div>
        `,
        width: '800px',
        showCloseButton: true
    });
}

function copyToClipboard() {
    const text = document.getElementById('api-endpoint').innerText;
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'API endpoint copied to clipboard',
            timer: 2000
        });
    });
}

function showApiInfo() {
    Swal.fire({
        title: 'API Integration Guide',
        html: `
            <div style="text-align: left;">
                <h5>Endpoint URL:</h5>
                <code>${$('#api-endpoint').text()}</code>
                <hr>
                <h5>Supported Formats:</h5>
                <ul>
                    <li>JSON (Recommended)</li>
                    <li>XML (Auto-converted to JSON)</li>
                </ul>
                <h5>Request Method:</h5>
                <p>POST</p>
                <h5>Sample Request:</h5>
                <pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto;">
{
  "Transactions": [
    {
      "LOCATION_CODE": "COL001",
      "TERMINAL_ID": "T001",
      "RCPT_NUM": "INV-001",
      "RCPT_DT": "20250205",
      "BUSINESS_DT": "20250205",
      "INV_AMT": 1000.00,
      "TRAN_STATUS": "SALES"
    }
  ]
}
                </pre>
            </div>
        `,
        width: '800px',
        showCloseButton: true
    });
}

function exportToCSV() {
    if (filteredData.length === 0) {
        Swal.fire('No Data', 'There is no data to export', 'info');
        return;
    }

    let csv = 'Date,Method,Endpoint,Status,Duration\n';
    filteredData.forEach(log => {
        csv += `"${log.created_at || ''}","${log.request_method || ''}","${log.endpoint || ''}","${log.status || ''}","${log.duration_ms || ''} ms"\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'colombo_sync_logs_' + new Date().getTime() + '.csv';
    a.click();
}

function printTable() {
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Colombo Sync Logs</title>');
    printWindow.document.write('<style>table {width: 100%; border-collapse: collapse;} th, td {border: 1px solid #ddd; padding: 8px; text-align: left;} th {background-color: #f2f2f2;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h2>Colombo City Center - Sync Logs</h2>');
    printWindow.document.write('<p>Generated: ' + new Date().toLocaleString() + '</p>');
    printWindow.document.write(document.getElementById('syncTable').outerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}
</script>
@endsection
