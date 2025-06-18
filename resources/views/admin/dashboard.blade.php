@extends('admin.layouts.app')

@section('title', 'Dashboard Admin')

@section('styles')
<!-- ApexCharts -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.40.0/dist/apexcharts.min.css">
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    .stat-card {
        border-left: 4px solid;
        border-radius: 4px;
    }
    
    .stat-card-primary {
        border-color: #bf6420;
    }
    
    .stat-card-success {
        border-color: #1cc88a;
    }
    
    .stat-card-info {
        border-color: #36b9cc;
    }
    
    .stat-card-warning {
        border-color: #f6c23e;
    }
    
    .stat-icon {
        font-size: 2rem;
        color: #dddfeb;
    }
    
    .stat-title {
        text-transform: uppercase;
        font-size: 0.7rem;
        font-weight: bold;
        color: #bf6420;
    }
    
    .stat-value {
        font-size: 1.25rem;
        font-weight: bold;
        color: #5a5c69;
    }
    
    #map {
        height: 500px;
    }
    
    .map-container {
        position: relative;
    }
    
    .map-legend {
        position: absolute;
        bottom: 25px;
        right: 10px;
        z-index: 999;
        background: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        margin: 5px 0;
    }
    
    .legend-color {
        width: 15px;
        height: 15px;
        margin-right: 5px;
        border-radius: 3px;
    }
    
    .density-legend {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #eee;
    }
    
    .density-gradient {
        height: 20px;
        width: 100%;
        background: linear-gradient(to right, rgba(191, 100, 32, 0.1), rgba(191, 100, 32, 1));
        margin: 5px 0;
    }
    
    .density-labels {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
    }
         .leaflet-attribution-flag {
            display: none !important;
        }

</style>
@endsection

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    <div>
        <form action="{{ route('admin.dashboard') }}" method="GET" class="d-flex">
            <div class="input-group me-2">
                <span class="input-group-text">Dari</span>
                <input type="date" class="form-control" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
            </div>
            <div class="input-group me-2">
                <span class="input-group-text">Sampai</span>
                <input type="date" class="form-control" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-filter me-1"></i> Filter
            </button>
        </form>
    </div>
</div>

<!-- Statistik Dasar -->
<div class="row">
    <!-- Total Users -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card stat-card-primary h-100 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="stat-title mb-1">Total Pengguna</div>
                        <div class="stat-value mb-0">{{ number_format($stats['users']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-person-fill stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Checklists -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card stat-card-success h-100 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="stat-title mb-1">Total Checklist</div>
                        <div class="stat-value mb-0">{{ number_format($stats['checklists']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clipboard-check stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Fauna -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card stat-card-info h-100 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="stat-title mb-1">Total Fauna</div>
                        <div class="stat-value mb-0">{{ number_format($stats['faunas']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-emoji-smile stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Users -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card stat-card-warning h-100 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="stat-title mb-1">Pengguna Baru ({{ $startDate->format('d M') }} - {{ $endDate->format('d M') }})</div>
                        <div class="stat-value mb-0">{{ number_format($stats['new_users']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-person-plus-fill stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chart Pengguna Baru -->
    <div class="col-xl-8 col-lg-7 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0">Pengguna Baru Per Hari</h6>
            </div>
            <div class="card-body">
                <div id="userChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- Chart Checklist Per Kategori -->
    <div class="col-xl-4 col-lg-5 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0">Checklist Per Kategori</h6>
            </div>
            <div class="card-body">
                <div id="categoryChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Peta Checklist -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0">Peta Lokasi Checklist</h6>
    </div>
    <div class="card-body">
        <div class="map-container">
            <div id="map"></div>
            <div class="map-legend">
                <h6 class="mb-2">Legenda</h6>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #bf6420;"></div>
                    <span>Marker Lokasi</span>
                </div>
                <div class="density-legend">
                    <h6 class="mb-1">Kepadatan Data</h6>
                    <div class="density-gradient"></div>
                    <div class="density-labels">
                        <span>Rendah</span>
                        <span>Tinggi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Checklist Terbaru -->
    <div class="col-lg-7 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0">Checklist Terbaru</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Lokasi</th>
                                <th>Tipe</th>
                                <th>User</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($latestChecklists as $checklist)
                            <tr>
                                <td>{{ $checklist->nama_lokasi }}</td>
                                <td>
                                    <span class="badge bg-info">
                                        @if(strtolower($checklist->type) === 'lainnya')
                                            Pemeliharaan & Penangkaran
                                        @else
                                            {{ $checklist->type }}
                                        @endif
                                    </span>
                                </td>
                                <td>{{ $checklist->user->name }}</td>
                                <td>{{ $checklist->tanggal->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.checklists.show', $checklist) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Aktivitas Terbaru -->
    <div class="col-lg-5 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0">Aktivitas Terbaru</h6>
            </div>
            <div class="card-body">
                <div class="timeline-container">
                    @foreach($activityLogs as $log)
                    <div class="timeline-item pb-3">
                        <div class="row">
                            <div class="col-auto">
                                <div class="timeline-icon bg-primary text-white rounded-circle p-2">
                                    <i class="bi bi-activity"></i>
                                </div>
                            </div>
                            <div class="col">
                                <div class="timeline-content">
                                    <h6 class="mb-0">{{ $log->user ? $log->user->name : 'System' }}</h6>
                                    <p class="mb-0">{{ $log->description }}</p>
                                    <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.40.0/dist/apexcharts.min.js"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    // User Chart
    const userChartData = @json($newUsersChart);
    
    const userChartOptions = {
        series: [{
            name: 'Pengguna Baru',
            data: userChartData
        }],
        chart: {
            type: 'area',
            height: 300,
            toolbar: {
                show: false
            }
        },
        colors: ['#bf6420'],
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        xaxis: {
            type: 'datetime',
            labels: {
                format: 'dd MMM'
            }
        },
        yaxis: {
            min: 0,
            labels: {
                formatter: function (value) {
                    return Math.round(value);
                }
            }
        },
        tooltip: {
            x: {
                format: 'dd MMM yyyy'
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.3
            }
        }
    };

    const userChart = new ApexCharts(document.querySelector("#userChart"), userChartOptions);
    userChart.render();

    // Category Chart
    const categoryData = @json($checklistsPerCategory);
    
    // Ganti label "lainnya" menjadi "Pemeliharaan & Penangkaran" jika ada
    categoryData.forEach(item => {
        if (item.category && item.category.toLowerCase() === 'lainnya') {
            item.category = 'Pemeliharaan & Penangkaran';
        }
    });
    
    const categoryChartOptions = {
        series: categoryData.map(item => item.total),
        chart: {
            type: 'donut',
            height: 300,
            toolbar: {
                show: false
            }
        },
        colors: ['#bf6420', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#6f42c1'],
        labels: categoryData.map(item => item.category),
        dataLabels: {
            enabled: true
        },
        responsive: [{
            breakpoint: 480,
            options: {
                legend: {
                    position: 'bottom'
                }
            }
        }],
        legend: {
            position: 'bottom'
        }
    };

    const categoryChart = new ApexCharts(document.querySelector("#categoryChart"), categoryChartOptions);
    categoryChart.render();
    
    // Map
    const mapData = @json($mapData);
    
    // Ganti tipe "lainnya" menjadi "Pemeliharaan & Penangkaran" jika ada
    mapData.forEach(point => {
        if (point.type && point.type.toLowerCase() === 'lainnya') {
            point.type = 'Pemeliharaan & Penangkaran';
        }
    });
    
    const map = L.map('map').setView([-2.5, 118], 5);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Grid system configuration
    const gridSizes = {
        extremelyLarge: 0.4,
        veryLarge: 0.3,
        large: 0.2,
        mediumLarge: 0.15,
        medium: 0.1,
        mediumSmall: 0.05,
        small: 0.02,
        verySmall: 0.01,
        tiny: 0.005
    };
    
    // Markers layer group
    const markersLayer = L.layerGroup().addTo(map);
    // Grid layer group
    const gridLayer = L.layerGroup().addTo(map);
    
    // Zoom level threshold for switching between grid and markers
    const MARKER_ZOOM_THRESHOLD = 12;
    
    // Function to get grid size based on zoom level
    function getGridSizeByZoom(zoom) {
        if (zoom <= 4) return gridSizes.extremelyLarge;
        if (zoom <= 5) return gridSizes.veryLarge;
        if (zoom <= 6) return gridSizes.large;
        if (zoom <= 7) return gridSizes.mediumLarge;
        if (zoom <= 8) return gridSizes.medium;
        if (zoom <= 9) return gridSizes.mediumSmall;
        if (zoom <= 10) return gridSizes.small;
        if (zoom <= 11) return gridSizes.verySmall;
        return gridSizes.tiny;
    }
    
    // Function to calculate opacity based on point count
    function getOpacityByCount(count, maxCount) {
        // Minimum opacity 0.2, maximum 0.9
        return Math.min(0.2 + (count / maxCount) * 0.7, 0.9);
    }
    
    // Throttle function to limit execution frequency
    function throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
    
    // Function to update grid
    function updateGrid() {
        gridLayer.clearLayers();
        
        const zoom = map.getZoom();
        
        // Only show grid when below marker threshold
        if (zoom < MARKER_ZOOM_THRESHOLD) {
            const bounds = map.getBounds();
            const gridSize = getGridSizeByZoom(zoom);
            
            // Calculate grid bounds
            const south = Math.floor(bounds.getSouth() / gridSize) * gridSize;
            const north = Math.ceil(bounds.getNorth() / gridSize) * gridSize;
            const west = Math.floor(bounds.getWest() / gridSize) * gridSize;
            const east = Math.ceil(bounds.getEast() / gridSize) * gridSize;
            
            // Create grid cells
            const gridCells = [];
            
            // First pass: count points in each cell
            for (let lat = south; lat < north; lat += gridSize) {
                for (let lng = west; lng < east; lng += gridSize) {
                    const pointsInCell = mapData.filter(point => 
                        point.latitude >= lat && 
                        point.latitude < lat + gridSize && 
                        point.longitude >= lng && 
                        point.longitude < lng + gridSize
                    );
                    
                    if (pointsInCell.length > 0) {
                        gridCells.push({
                            bounds: [
                                [lat, lng],
                                [lat + gridSize, lng],
                                [lat + gridSize, lng + gridSize],
                                [lat, lng + gridSize]
                            ],
                            count: pointsInCell.length,
                            points: pointsInCell
                        });
                    }
                }
            }
            
            // Find max count for opacity calculation
            const maxCount = Math.max(...gridCells.map(cell => cell.count), 1);
            
            // Second pass: create grid polygons
            gridCells.forEach(cell => {
                const opacity = getOpacityByCount(cell.count, maxCount);
                
                const polygon = L.polygon(cell.bounds, {
                    color: '#bf6420',
                    fillColor: '#bf6420',
                    fillOpacity: opacity,
                    weight: 1,
                    opacity: 0.5
                }).addTo(gridLayer);
                
                // Add popup with info
                polygon.bindPopup(`
                    <b>Area Grid</b><br>
                    Jumlah lokasi: ${cell.count}<br>
                    Koordinat: ${cell.bounds[0][0].toFixed(3)}, ${cell.bounds[0][1].toFixed(3)}
                `);
            });
        }
        
        // Update legend visibility
        updateLegend();
    }
    
    // Update markers based on viewport - only on very high zoom levels
    function updateMarkers() {
        markersLayer.clearLayers();
        
        const zoom = map.getZoom();
        
        // Only show markers at very high zoom levels (above threshold)
        if (zoom >= MARKER_ZOOM_THRESHOLD) {
            const bounds = map.getBounds();
            
            // Use a more efficient method to filter points
            const visibleMarkers = [];
            const maxMarkers = 200; // Increased limit for better visibility
            
            for (let i = 0; i < mapData.length && visibleMarkers.length < maxMarkers; i++) {
                const point = mapData[i];
                if (point.latitude && point.longitude && 
                    bounds.contains([point.latitude, point.longitude])) {
                    visibleMarkers.push(point);
                }
            }
            
            // Add markers in batches to prevent UI freeze
            setTimeout(() => {
                visibleMarkers.forEach(point => {
                    L.circleMarker([point.latitude, point.longitude], {
                        radius: 5,
                        color: '#000',
                        fillColor: '#bf6420',
                        fillOpacity: 0.8,
                        weight: 1
                    }).addTo(markersLayer).bindPopup(`
                        <b>${point.nama_lokasi}</b><br>
                        ID: ${point.id}<br>
                        Tipe: ${point.type}
                    `);
                });
            }, 100);
        }
        
        // Update legend visibility
        updateLegend();
    }
    
    // Update legend based on current display mode
    function updateLegend() {
        const zoom = map.getZoom();
        const densityLegend = document.querySelector('.density-legend');
        const markerLegend = document.querySelector('.legend-item');
        
        if (zoom >= MARKER_ZOOM_THRESHOLD) {
            // Show marker legend, hide density legend
            if (densityLegend) densityLegend.style.display = 'none';
            if (markerLegend) markerLegend.style.display = 'flex';
        } else {
            // Show density legend, hide marker legend
            if (densityLegend) densityLegend.style.display = 'block';
            if (markerLegend) markerLegend.style.display = 'none';
        }
    }
    
    // Throttled update functions
    const throttledUpdateGrid = throttle(updateGrid, 300);
    const throttledUpdateMarkers = throttle(updateMarkers, 500);
    
    // Initial update based on current zoom level
    function initialUpdate() {
        const zoom = map.getZoom();
        
        if (zoom >= MARKER_ZOOM_THRESHOLD) {
            // If zoomed in, show markers
            updateMarkers();
        } else {
            // If zoomed out, show grid
            updateGrid();
        }
        
        // Update legend
        updateLegend();
    }
    
    // Run initial update
    initialUpdate();
    
    // Update on map move/zoom
    map.on('zoomend moveend', function() {
        const zoom = map.getZoom();
        
        if (zoom >= MARKER_ZOOM_THRESHOLD) {
            // Clear grid and show markers
            gridLayer.clearLayers();
            throttledUpdateMarkers();
        } else {
            // Clear markers and show grid
            markersLayer.clearLayers();
            throttledUpdateGrid();
        }
    });
</script>
@endsection 