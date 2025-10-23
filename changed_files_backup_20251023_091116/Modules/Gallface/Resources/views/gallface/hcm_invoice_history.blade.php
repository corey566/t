
@extends('layouts.app')

@section('title', 'HCM Invoice History')

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
    
    .stats-card {
        border-radius: 8px;
        padding: 20px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .stats-card h3 {
        font-size: 2rem;
        margin: 0;
        font-weight: bold;
    }
    .stats-card p {
        margin: 5px 0 0 0;
        opacity: 0.9;
    }
    .stats-card.blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .stats-card.green { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
    .stats-card.orange { background: linear-gradient(135deg, #fa9352 0%, #ffcc00 100%); }
    
    .sync-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }
    
    .sync-card h3 {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
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
    
    .form-control-modern {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .form-control-modern:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
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
    
    .btn-back {
        background: #6c757d;
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-back:hover {
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
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
    
    .invoice-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .invoice-table th {
        background: #f8f9fa;
        padding: 15px 12px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #e0e0e0;
        position: sticky;
        top: 0;
        cursor: pointer;
        white-space: nowrap;
    }
    
    .invoice-table th:hover {
        background: #e9ecef;
    }
    
    .invoice-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .invoice-table tr:hover {
        background: #f8f9fa;
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
    
    .status-badge.synced {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
    }
    
    .status-badge.pending {
        background: linear-gradient(135deg, #fa9352 0%, #ffcc00 100%);
        color: white;
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
</style>

<div class="gallface-container">
    @include('gallface::layouts.nav')
    
    <div class="gallface-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 style="font-size: 24px; font-weight: 700; margin: 0;">
                <i class="fas fa-file-invoice"></i> HCM Invoice History
            </h1>
            <a href="{{ url('gallface/hcm/credentials') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Settings
            </a>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row" style="margin: 0;">
        <div class="col-md-4" style="padding: 0 10px 0 0;">
            <div class="stats-card blue">
                <h3 id="totalInvoices">{{ $stats['total_invoices'] }}</h3>
                <p>Total Invoices</p>
            </div>
        </div>
        <div class="col-md-4" style="padding: 0 10px;">
            <div class="stats-card green">
                <h3 id="syncedInvoices">{{ $stats['synced_invoices'] }}</h3>
                <p>Synced to HCM</p>
            </div>
        </div>
        <div class="col-md-4" style="padding: 0 0 0 10px;">
            <div class="stats-card orange">
                <h3 id="pendingInvoices">{{ $stats['not_synced_invoices'] }}</h3>
                <p>Pending Sync</p>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="sync-card">
        <h3><i class="fas fa-filter"></i> Filter Invoices</h3>
        <div class="filters-section">
            <form id="filterForm" class="filter-form">
                <div class="filter-group">
                    <label>Date From</label>
                    <input type="date" id="date-from" class="form-control-modern" value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
                </div>
                <div class="filter-group">
                    <label>Date To</label>
                    <input type="date" id="date-to" class="form-control-modern" value="{{ request('date_to', now()->format('Y-m-d')) }}">
                </div>
                <div class="filter-group">
                    <label>Sync Status</label>
                    <select id="sync-status" class="form-control-modern">
                        <option value="all">All Invoices</option>
                        <option value="synced">Synced Only</option>
                        <option value="not_synced">Not Synced Only</option>
                    </select>
                </div>
                <div class="filter-group" style="flex: 0;">
                    <button type="button" id="applyFilters" class="btn-filter">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Invoice Table -->
    <div class="sync-card">
        <h3><i class="fas fa-list"></i> Invoice List</h3>
        
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
                <button class="btn-export" id="exportExcel"><i class="fas fa-file-excel"></i> Export Excel</button>
                <button class="btn-export" id="printTable"><i class="fas fa-print"></i> Print</button>
            </div>
            <div>
                <input type="text" id="searchInput" class="form-control-modern" placeholder="Search..." style="width: 250px;">
            </div>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="invoice-table" id="invoiceTable">
                <thead>
                    <tr>
                        <th data-sort="invoice_no">Invoice No <i class="fas fa-sort"></i></th>
                        <th data-sort="transaction_date">Date <i class="fas fa-sort"></i></th>
                        <th data-sort="customer_name">Customer <i class="fas fa-sort"></i></th>
                        <th data-sort="customer_mobile">Mobile <i class="fas fa-sort"></i></th>
                        <th data-sort="type">Type <i class="fas fa-sort"></i></th>
                        <th data-sort="final_total">Amount (LKR) <i class="fas fa-sort"></i></th>
                        <th data-sort="tax_amount">Tax <i class="fas fa-sort"></i></th>
                        <th data-sort="status">Status <i class="fas fa-sort"></i></th>
                        <th data-sort="hcm_synced_at">Synced At <i class="fas fa-sort"></i></th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="9" class="no-data" style="text-align: center; padding: 40px;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div id="showingInfo" style="color: #666;">Showing 0 to 0 of 0 entries</div>
            <div id="pagination" style="display: flex; gap: 10px;"></div>
        </div>
    </div>
    
    <div style="text-align: center; padding: 20px; color: #999; font-size: 13px;">
        ZIMOZI POS - v6.1 | Copyright © 2025 All rights reserved.
    </div>
</div>
@endsection

@section('javascript')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentPage = 1;
let invoicesData = [];
let filteredData = [];
let sortColumn = '';
let sortDirection = 'asc';
const locationId = {{ $location_id }};

$(document).ready(function() {
    loadInvoices();
    
    // Apply filters
    $('#applyFilters').click(function() {
        currentPage = 1;
        loadInvoices();
    });
    
    // Search functionality
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            applyFiltersAndSearch();
        }, 300);
    });
    
    // Per page change
    $('#perPage').change(function() {
        currentPage = 1;
        renderTable();
    });
    
    // Table sorting
    $(document).on('click', '.invoice-table th[data-sort]', function() {
        const column = $(this).data('sort');
        if (sortColumn === column) {
            sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            sortColumn = column;
            sortDirection = 'asc';
        }
        
        // Update sort icons
        $('.invoice-table th i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
        const icon = $(this).find('i');
        icon.removeClass('fa-sort').addClass(sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
        
        applyFiltersAndSearch();
    });
    
    // Export CSV
    $('#exportCSV').click(function() {
        exportToCSV();
    });
    
    // Export Excel
    $('#exportExcel').click(function() {
        window.location.href = '{{ url("gallface/hcm/location/" . $location_id . "/download-excel") }}?date_from=' + $('#date-from').val() + '&date_to=' + $('#date-to').val();
    });
    
    // Print
    $('#printTable').click(function() {
        printTable();
    });
});

function loadInvoices() {
    const dateFrom = $('#date-from').val();
    const dateTo = $('#date-to').val();
    const syncStatus = $('#sync-status').val();
    
    $('#tableBody').html('<tr><td colspan="9" style="text-align: center; padding: 40px;">Loading...</td></tr>');
    
    $.ajax({
        url: '{{ url("gallface/hcm/location/" . $location_id . "/invoice-history") }}',
        type: 'GET',
        data: {
            date_from: dateFrom,
            date_to: dateTo,
            sync_status: syncStatus
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                invoicesData = response.invoices.data;
                
                // Update stats
                $('#totalInvoices').text(response.stats.total_invoices);
                $('#syncedInvoices').text(response.stats.synced_invoices);
                $('#pendingInvoices').text(response.stats.not_synced_invoices);
                
                applyFiltersAndSearch();
            } else {
                $('#tableBody').html('<tr><td colspan="9" style="text-align: center; padding: 40px;">Failed to load data</td></tr>');
            }
        },
        error: function() {
            $('#tableBody').html('<tr><td colspan="9" style="text-align: center; padding: 40px;">Error loading data</td></tr>');
        }
    });
}

function applyFiltersAndSearch() {
    const searchTerm = $('#searchInput').val().toLowerCase();
    
    filteredData = invoicesData.filter(invoice => {
        const searchMatch = !searchTerm || 
            (invoice.invoice_no && invoice.invoice_no.toLowerCase().includes(searchTerm)) ||
            (invoice.customer_name && invoice.customer_name.toLowerCase().includes(searchTerm)) ||
            (invoice.customer_mobile && invoice.customer_mobile.includes(searchTerm));
        
        return searchMatch;
    });
    
    // Apply sorting
    if (sortColumn) {
        filteredData.sort((a, b) => {
            let aVal = a[sortColumn] || '';
            let bVal = b[sortColumn] || '';
            
            if (sortColumn === 'final_total' || sortColumn === 'tax_amount') {
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
    const perPage = parseInt($('#perPage').val());
    const start = (currentPage - 1) * perPage;
    const end = start + perPage;
    const pageData = filteredData.slice(start, end);
    
    if (pageData.length === 0) {
        $('#tableBody').html('<tr><td colspan="9" style="text-align: center; padding: 40px;"><i class="fas fa-inbox fa-3x text-muted" style="display: block; margin-bottom: 15px;"></i><p class="text-muted">No invoices found for the selected criteria.</p></td></tr>');
        $('#showingInfo').text('Showing 0 to 0 of 0 entries');
        $('#pagination').html('');
        return;
    }
    
    let html = '';
    pageData.forEach(invoice => {
        const statusBadge = invoice.hcm_synced_at 
            ? '<span class="status-badge synced"><i class="fas fa-check-circle"></i> Synced</span>'
            : '<span class="status-badge pending"><i class="fas fa-clock"></i> Pending</span>';
        
        const syncedAt = invoice.hcm_synced_at 
            ? new Date(invoice.hcm_synced_at).toLocaleString()
            : '-';
        
        const typeLabel = invoice.is_gift_voucher 
            ? '<span style="background: linear-gradient(135deg, #fa9352 0%, #ffcc00 100%); color: white; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">Gift Voucher</span>'
            : '<span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">' + (invoice.type ? invoice.type.charAt(0).toUpperCase() + invoice.type.slice(1) : 'Sale') + '</span>';
        
        html += `<tr>
            <td><strong>${invoice.invoice_no || '-'}</strong></td>
            <td>${invoice.transaction_date ? new Date(invoice.transaction_date).toLocaleString() : '-'}</td>
            <td>${invoice.customer_name || 'Walk-in Customer'}</td>
            <td>${invoice.customer_mobile || '-'}</td>
            <td>${typeLabel}</td>
            <td>${parseFloat(invoice.final_total || 0).toFixed(2)}</td>
            <td>${parseFloat(invoice.tax_amount || 0).toFixed(2)}</td>
            <td>${statusBadge}</td>
            <td>${syncedAt}</td>
        </tr>`;
    });
    
    $('#tableBody').html(html);
    
    // Update info
    const showing = `Showing ${start + 1} to ${Math.min(end, filteredData.length)} of ${filteredData.length} entries`;
    $('#showingInfo').text(showing);
    
    // Render pagination
    renderPagination(perPage);
}

function renderPagination(perPage) {
    const totalPages = Math.ceil(filteredData.length / perPage);
    let html = '';
    
    // Previous button
    html += `<button class="btn-export" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})">Previous</button>`;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `<button class="btn-export ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += `<span style="padding: 0 10px;">...</span>`;
        }
    }
    
    // Next button
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

function exportToCSV() {
    if (filteredData.length === 0) {
        Swal.fire('No Data', 'There is no data to export', 'info');
        return;
    }
    
    let csv = 'Invoice No,Date,Customer,Mobile,Type,Amount (LKR),Tax,Status,Synced At\n';
    
    filteredData.forEach(invoice => {
        csv += `"${invoice.invoice_no || ''}",`;
        csv += `"${invoice.transaction_date || ''}",`;
        csv += `"${invoice.customer_name || 'Walk-in Customer'}",`;
        csv += `"${invoice.customer_mobile || ''}",`;
        csv += `"${invoice.is_gift_voucher ? 'Gift Voucher' : (invoice.type || 'Sale')}",`;
        csv += `"${parseFloat(invoice.final_total || 0).toFixed(2)}",`;
        csv += `"${parseFloat(invoice.tax_amount || 0).toFixed(2)}",`;
        csv += `"${invoice.hcm_synced_at ? 'Synced' : 'Pending'}",`;
        csv += `"${invoice.hcm_synced_at || ''}"\n`;
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'hcm_invoices_' + new Date().getTime() + '.csv';
    a.click();
}

function printTable() {
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>HCM Invoice History</title>');
    printWindow.document.write('<style>table {width: 100%; border-collapse: collapse;} th, td {border: 1px solid #ddd; padding: 8px; text-align: left;} th {background-color: #f2f2f2;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h2>HCM Integration - Invoice History</h2>');
    printWindow.document.write('<p>Generated: ' + new Date().toLocaleString() + '</p>');
    printWindow.document.write(document.getElementById('invoiceTable').outerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}
</script>
@endsection
