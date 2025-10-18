
@extends('layouts.app')

@section('title', 'Auto-Sync Monitor')

@section('content')
<style>
    .monitor-container {
        background-color: #f5f5f5;
        min-height: 100vh;
        padding: 20px;
    }
    
    .monitor-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .status-indicator {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 8px;
    }
    
    .status-active { background: #43e97b; }
    .status-inactive { background: #fa9352; }
    .status-error { background: #ff4757; }
    
    .sync-log {
        max-height: 400px;
        overflow-y: auto;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        font-family: monospace;
        font-size: 13px;
    }
</style>

<div class="monitor-container">
    @include('gallface::layouts.nav')
    
    <div class="monitor-card">
        <h2><i class="fas fa-sync-alt"></i> Auto-Sync Monitor</h2>
        <p>Real-time monitoring of automatic sales synchronization</p>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="monitor-card">
                <h3><i class="fas fa-building"></i> Gallface Auto-Sync</h3>
                <div id="gallface-status">
                    <span class="status-indicator status-active"></span>
                    <span>Active - Last sync: <span id="gallface-last-sync">-</span></span>
                </div>
                <div class="sync-log" id="gallface-log">
                    Loading logs...
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="monitor-card">
                <h3><i class="fas fa-hospital"></i> HCM Auto-Sync</h3>
                <div id="hcm-status">
                    <span class="status-indicator status-active"></span>
                    <span>Active - Last sync: <span id="hcm-last-sync">-</span></span>
                </div>
                <div class="sync-log" id="hcm-log">
                    Loading logs...
                </div>
            </div>
        </div>
    </div>
    
    <div class="monitor-card">
        <h3><i class="fas fa-list"></i> Active Locations</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Location</th>
                    <th>Mall</th>
                    <th>Auto-Sync</th>
                    <th>Last Synced</th>
                    <th>Pending Sales</th>
                </tr>
            </thead>
            <tbody id="locations-table">
                <tr><td colspan="5" class="text-center">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    function loadMonitorData() {
        $.get('{{ url("gallface/monitor/status") }}', function(data) {
            if (data.success) {
                // Update Gallface status
                $('#gallface-last-sync').text(data.gallface.last_sync || 'Never');
                $('#gallface-log').html(data.gallface.logs || 'No recent logs');
                
                // Update HCM status
                $('#hcm-last-sync').text(data.hcm.last_sync || 'Never');
                $('#hcm-log').html(data.hcm.logs || 'No recent logs');
                
                // Update locations table
                let html = '';
                data.locations.forEach(function(loc) {
                    html += `<tr>
                        <td>${loc.name}</td>
                        <td>${loc.mall_code.toUpperCase()}</td>
                        <td>${loc.auto_sync_enabled ? '<span class="badge badge-success">Enabled</span>' : '<span class="badge badge-secondary">Disabled</span>'}</td>
                        <td>${loc.last_synced_at || 'Never'}</td>
                        <td>${loc.pending_sales}</td>
                    </tr>`;
                });
                $('#locations-table').html(html);
            }
        });
    }
    
    // Load data initially
    loadMonitorData();
    
    // Refresh every 30 seconds
    setInterval(loadMonitorData, 30000);
});
</script>
@endsection
