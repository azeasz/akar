@extends('admin.layouts.app')

@section('title', 'Test API Priority Fauna')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Test API Endpoints</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Test Basic Endpoint</h6>
                            <button class="btn btn-primary" onclick="testBasicEndpoint()">Test Basic</button>
                            <div id="basicResult" class="mt-2"></div>
                        </div>
                        <div class="col-md-6">
                            <h6>Test Taxa Search</h6>
                            <input type="text" id="searchQuery" class="form-control mb-2" placeholder="Enter search query" value="komodo">
                            <button class="btn btn-success" onclick="testTaxaSearch()">Test Search</button>
                            <div id="searchResult" class="mt-2"></div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6>Debug Information</h6>
                            <div id="debugInfo" class="bg-light p-3 rounded"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testBasicEndpoint() {
    const resultDiv = document.getElementById('basicResult');
    resultDiv.innerHTML = '<div class="spinner-border spinner-border-sm"></div> Testing...';
    
    fetch('{{ route("admin.priority-fauna.api.test") }}')
        .then(response => {
            console.log('Basic test response:', response);
            return response.json();
        })
        .then(data => {
            console.log('Basic test data:', data);
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <strong>Success!</strong><br>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                </div>
            `;
        })
        .catch(error => {
            console.error('Basic test error:', error);
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <strong>Error:</strong> ${error.message}
                </div>
            `;
        });
}

function testTaxaSearch() {
    const query = document.getElementById('searchQuery').value;
    const resultDiv = document.getElementById('searchResult');
    
    if (!query) {
        alert('Please enter a search query');
        return;
    }
    
    resultDiv.innerHTML = '<div class="spinner-border spinner-border-sm"></div> Searching...';
    
    const url = `{{ route("admin.priority-fauna.api.taxa-suggestions") }}?q=${encodeURIComponent(query)}&limit=5`;
    console.log('Search URL:', url);
    
    fetch(url)
        .then(response => {
            console.log('Search response:', response);
            return response.json();
        })
        .then(data => {
            console.log('Search data:', data);
            resultDiv.innerHTML = `
                <div class="alert alert-info">
                    <strong>Search Results:</strong><br>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                </div>
            `;
        })
        .catch(error => {
            console.error('Search error:', error);
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <strong>Error:</strong> ${error.message}
                </div>
            `;
        });
}

// Load debug info on page load
document.addEventListener('DOMContentLoaded', function() {
    const debugInfo = {
        'Current URL': window.location.href,
        'Base URL': '{{ url("/") }}',
        'Test Endpoint': '{{ route("admin.priority-fauna.api.test") }}',
        'Taxa Search Endpoint': '{{ route("admin.priority-fauna.api.taxa-suggestions") }}',
        'CSRF Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 'Not found',
        'User Agent': navigator.userAgent,
        'Timestamp': new Date().toISOString()
    };
    
    document.getElementById('debugInfo').innerHTML = `<pre>${JSON.stringify(debugInfo, null, 2)}</pre>`;
});
</script>
@endsection
