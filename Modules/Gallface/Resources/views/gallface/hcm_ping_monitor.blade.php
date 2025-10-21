
@extends('layouts.app')

@section('title', 'HCM Ping Monitor')

@section('content')
<style>
    .ping-monitor-container {
        background-color: #1e1e1e;
        color: #d4d4d4;
        min-height: 100vh;
        padding: 20px;
        font-family: 'Courier New', monospace;
    }

    .terminal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 8px 8px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .terminal-body {
        background: #1e1e1e;
        border: 1px solid #333;
        border-radius: 0 0 8px 8px;
        padding: 20px;
        height: 600px;
        overflow-y: auto;
    }

    .log-entry {
        padding: 8px 0;
        border-bottom: 1px solid #2a2a2a;
        font-size: 13px;
    }

    .log-entry.success {
        color: #4ec9b0;
    }

    .log-entry.error {
        color: #f48771;
    }

    .log-timestamp {
        color: #858585;
        margin-right: 10px;
    }

    .log-user {
        color: #dcdcaa;
        font-weight: bold;
    }

    .log-ip {
        color: #569cd6;
    }

    .ping-controls {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .btn-terminal {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-terminal.stop {
        background: linear-gradient(135deg, #fc5c7d 0%, #f05454 100%);
    }

    .btn-terminal.clear {
        background: #6c757d;
    }

    .status-indicator {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 8px;
    }

    .status-indicator.active {
        background: #43e97b;
        animation: pulse 2s infinite;
    }

    .status-indicator.inactive {
        background: #f05454;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>

<div class="ping-monitor-container">
    @include('gallface::layouts.nav')

    <div class="ping-controls">
        <button class="btn-terminal" id="startPing">
            <i class="fas fa-play"></i> Start Ping
        </button>
        <button class="btn-terminal stop" id="stopPing" style="display: none;">
            <i class="fas fa-stop"></i> Stop Ping
        </button>
        <button class="btn-terminal clear" id="clearLogs">
            <i class="fas fa-eraser"></i> Clear Logs
        </button>
        <a href="{{ url('gallface/hcm/credentials') }}" class="btn-terminal" style="background: #6c757d; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="terminal-header">
        <div>
            <span class="status-indicator" id="statusIndicator"></span>
            <strong>HCM Ping Monitor - Location: {{ $location->name }}</strong>
        </div>
        <div>
            <span id="pingStatus">Stopped</span>
        </div>
    </div>

    <div class="terminal-body" id="terminalBody">
        <div class="log-entry" style="color: #858585;">
            Waiting to start ping monitoring...
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let pingInterval = null;
    let lastTimestamp = null;
    const locationId = {{ $location_id }};

    function addLog(message, type = 'info') {
        const timestamp = new Date().toLocaleString();
        let logClass = type === 'success' ? 'success' : (type === 'error' ? 'error' : '');
        
        $('#terminalBody').prepend(`
            <div class="log-entry ${logClass}">
                <span class="log-timestamp">[${timestamp}]</span>
                ${message}
            </div>
        `);
    }

    function sendPing() {
        $.ajax({
            url: '/gallface/hcm/location/' + locationId + '/ping',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    const userInfo = response.user_info || {};
                    addLog(
                        `✓ PING SUCCESS - User: <span class="log-user">${userInfo.username || 'Unknown'}</span> | IP: <span class="log-ip">${userInfo.ip_address || 'N/A'}</span> | ${response.message}`,
                        'success'
                    );
                } else {
                    addLog(`✗ PING FAILED - ${response.message}`, 'error');
                }
            },
            error: function(xhr) {
                addLog(`✗ PING ERROR - ${xhr.responseJSON?.message || 'Network error'}`, 'error');
            }
        });
    }

    function fetchPingLogs() {
        $.ajax({
            url: '/gallface/hcm/location/' + locationId + '/ping-logs',
            type: 'GET',
            data: { since: lastTimestamp, limit: 10 },
            success: function(response) {
                if (response.success && response.logs.length > 0) {
                    response.logs.reverse().forEach(function(log) {
                        const timestamp = new Date(log.created_at).toLocaleString();
                        const type = log.success ? 'success' : 'error';
                        const icon = log.success ? '✓' : '✗';
                        
                        $('#terminalBody').prepend(`
                            <div class="log-entry ${type}">
                                <span class="log-timestamp">[${timestamp}]</span>
                                ${icon} User: <span class="log-user">${log.username}</span> | IP: <span class="log-ip">${log.ip_address}</span> | ${log.message}
                            </div>
                        `);
                    });
                    
                    lastTimestamp = response.logs[0].created_at;
                }
            }
        });
    }

    $('#startPing').click(function() {
        $(this).hide();
        $('#stopPing').show();
        $('#statusIndicator').addClass('active').removeClass('inactive');
        $('#pingStatus').text('Active - Pinging every 5 seconds');
        
        addLog('Ping monitoring started', 'success');
        
        // Send first ping immediately
        sendPing();
        
        // Then ping every 5 seconds
        pingInterval = setInterval(function() {
            sendPing();
        }, 5000);

        // Fetch logs every 2 seconds
        setInterval(fetchPingLogs, 2000);
    });

    $('#stopPing').click(function() {
        $(this).hide();
        $('#startPing').show();
        $('#statusIndicator').removeClass('active').addClass('inactive');
        $('#pingStatus').text('Stopped');
        
        clearInterval(pingInterval);
        pingInterval = null;
        
        addLog('Ping monitoring stopped', 'error');
    });

    $('#clearLogs').click(function() {
        $('#terminalBody').html('<div class="log-entry" style="color: #858585;">Logs cleared...</div>');
    });
});
</script>
@endsection
