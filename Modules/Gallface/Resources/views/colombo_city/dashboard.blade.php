
@extends('gallface::layouts.master')

@section('title', 'Colombo City Center Integration')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-building"></i> Colombo City Center Integration Dashboard
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-receipt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Transactions</span>
                                    <span class="info-box-number">{{ number_format($totalTransactions) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-calendar-day"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Today's Transactions</span>
                                    <span class="info-box-number">{{ number_format($todayTransactions) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Today's Sales</span>
                                    <span class="info-box-number">LKR {{ number_format($todaySales, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- API Information -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>API Endpoint Information</h4>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <h5><i class="icon fas fa-info-circle"></i> API Endpoint URL:</h5>
                                        <code id="api-endpoint">{{ url('/api/gallface/colombo-city/sales-data') }}</code>
                                        <button class="btn btn-sm btn-primary ml-2" onclick="copyToClipboard()">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                    
                                    <h5>Supported Formats:</h5>
                                    <ul>
                                        <li>JSON (Recommended)</li>
                                        <li>XML (Auto-converted to JSON)</li>
                                    </ul>
                                    
                                    <h5>Authentication:</h5>
                                    <p>Use API token authentication with Bearer token in header</p>
                                    
                                    <h5>Data Structure:</h5>
                                    <pre class="bg-light p-3">
{
  "Transactions": [
    {
      "LOCATION_CODE": "string",
      "TERMINAL_ID": "string",
      "SHIFT_NO": "string",
      "RCPT_NUM": "string",
      "RCPT_DT": "YYYYMMDD",
      "BUSINESS_DT": "YYYYMMDD",
      "RCPT_TM": "HHMMSS",
      "INV_AMT": "decimal",
      "TAX_AMT": "decimal",
      "TRAN_STATUS": "SALES|RETURN",
      "ItemDetail": [...],
      "PaymentDetail": [...]
    }
  ]
}
                                    </pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Mapping -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Business Location Mapping</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Business Location</th>
                                                <th>Colombo City Code</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($locations as $location)
                                            <tr>
                                                <td>{{ $location->name }}</td>
                                                <td>
                                                    <input type="text" class="form-control" 
                                                           id="location_code_{{ $location->id }}" 
                                                           placeholder="Enter Colombo City location code">
                                                </td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm" 
                                                            onclick="saveMapping({{ $location->id }})">
                                                        <i class="fas fa-save"></i> Save
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard() {
    const text = document.getElementById('api-endpoint').innerText;
    navigator.clipboard.writeText(text).then(() => {
        alert('API endpoint copied to clipboard!');
    });
}

function saveMapping(locationId) {
    const code = document.getElementById('location_code_' + locationId).value;
    
    if (!code) {
        alert('Please enter a location code');
        return;
    }
    
    $.ajax({
        url: '/gallface/colombo-city/save-location-mapping',
        method: 'POST',
        data: {
            business_location_id: locationId,
            colombo_location_code: code,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                alert('Location mapping saved successfully');
            }
        },
        error: function(xhr) {
            alert('Error saving location mapping');
        }
    });
}
</script>
@endsection
