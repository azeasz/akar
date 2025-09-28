@extends('admin.layouts.app')

@section('title', 'Tambah Fauna Prioritas')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-plus-circle text-primary"></i>
            Tambah Fauna Prioritas
        </h1>
        <a href="{{ route('admin.priority-fauna.fauna') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Fauna</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.priority-fauna.fauna.store') }}" method="POST" id="faunaForm">
                        @csrf
                        
                        <!-- Taxa Search -->
                        <div class="mb-4">
                            <label for="taxa_search" class="form-label">
                                <i class="bi bi-search"></i> Cari Taksa
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="taxa_search" 
                                       placeholder="Ketik nama taksa untuk mencari..." autocomplete="off">
                                <button type="button" class="btn btn-outline-secondary" id="clearSearch" onclick="document.getElementById('taxa_search').value='';">
                                    <i class="bi bi-x"></i>
                                </button>
                                <!-- Hidden Test Buttons - Uncomment for debugging -->
                                <button type="button" class="btn btn-outline-info" id="testSearch" title="Test dengan data dummy" onclick="testSearchDisplay()" style="display: none;">
                                    <i class="bi bi-gear"></i>
                                </button>
                                <button type="button" class="btn btn-outline-warning" id="debugBtn" title="Debug Info" onclick="testDebugInfo()" style="display: none;">
                                    <i class="bi bi-bug"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                Mulai ketik minimal 2 karakter untuk mencari taksa dari database Amaturalist
                                
                                <!-- Hidden Test Buttons - Uncomment for debugging -->
                                <div style="display: none;">
                                    <br><small class="text-info">💡 Test buttons (hidden for production)</small>
                                    <br><button type="button" class="btn btn-sm btn-success mt-2" onclick="alert('JavaScript berfungsi!'); console.log('Direct test successful');">
                                        <i class="bi bi-check-circle"></i> Test JavaScript
                                    </button>
                                    <button type="button" class="btn btn-sm btn-info mt-2 ms-2" onclick="console.log('Button clicked:', new Date()); alert('Console test - check F12');">
                                        <i class="bi bi-terminal"></i> Test Console
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning mt-2 ms-2" onclick="testBasicFunction()">
                                        <i class="bi bi-play"></i> Test Inline Function
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary mt-2 ms-2" onclick="testPerenaSearch()">
                                        <i class="bi bi-search"></i> Test Perena
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary mt-2 ms-2" onclick="testFormData()">
                                        <i class="bi bi-list-check"></i> Test Form Data
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Search Results -->
                            <div id="searchResults" class="mt-2" style="display: none; position: relative; z-index: 1000;">
                                <div class="list-group shadow-sm" id="resultsList" style="max-height: 300px; overflow-y: auto; border-radius: 0.375rem;"></div>
                            </div>
                        </div>

                        <!-- Selected Taxa Info -->
                        <div id="selectedTaxaInfo" style="display: none;">
                            <div class="alert alert-info">
                                <h6><i class="bi bi-check-circle"></i> Taksa Terpilih:</h6>
                                <div id="selectedTaxaDetails"></div>
                            </div>
                        </div>

                        <!-- Hidden Fields -->
                        <input type="hidden" id="taxa_id" name="taxa_id" required>
                        <input type="hidden" id="taxa_name" name="taxa_name" required>
                        <input type="hidden" id="scientific_name" name="scientific_name">
                        <input type="hidden" id="common_name" name="common_name">
                        <input type="hidden" id="iucn_status" name="iucn_status">
                        <input type="hidden" id="protection_status" name="protection_status">
                        <input type="hidden" id="taxa_rank" name="taxa_rank">
                        <input type="hidden" id="taxa_kingdom" name="taxa_kingdom">
                        <input type="hidden" id="taxa_phylum" name="taxa_phylum">
                        <input type="hidden" id="taxa_class" name="taxa_class">
                        <input type="hidden" id="taxa_order" name="taxa_order">
                        <input type="hidden" id="taxa_family" name="taxa_family">
                        <input type="hidden" id="taxa_genus" name="taxa_genus">
                        <input type="hidden" id="taxa_species" name="taxa_species">
                        <input type="hidden" id="taxa_data_json" name="taxa_data_json">

                        <!-- Category Selection -->
                        <div class="mb-3">
                            <label for="category_id" class="form-label">
                                <i class="bi bi-tag"></i> Kategori Prioritas <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }} - {{ $category->description }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">
                                <i class="bi bi-journal-text"></i> Catatan
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4" 
                                      placeholder="Catatan tambahan tentang fauna ini...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Monitoring Status -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_monitored" 
                                       name="is_monitored" value="1" {{ old('is_monitored', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_monitored">
                                    <i class="bi bi-eye"></i> Aktifkan monitoring untuk fauna ini
                                </label>
                            </div>
                            <div class="form-text">
                                Fauna yang dimonitor akan disinkronisasi secara berkala dengan API Amaturalist
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                <i class="bi bi-save"></i> Simpan Fauna
                            </button>
                            <a href="{{ route('admin.priority-fauna.fauna') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Help Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="bi bi-info-circle"></i> Panduan
                    </h6>
                </div>
                <div class="card-body">
                    <h6>Cara Menambah Fauna:</h6>
                    <ol class="small">
                        <li>Cari taksa menggunakan kolom pencarian</li>
                        <li>Pilih taksa dari hasil pencarian</li>
                        <li>Pilih kategori prioritas yang sesuai</li>
                        <li>Tambahkan catatan jika diperlukan</li>
                        <li>Aktifkan monitoring jika ingin data disinkronisasi</li>
                        <li>Klik "Simpan Fauna"</li>
                    </ol>
                    
                    <hr>
                    
                    <h6>Kategori Prioritas:</h6>
                    <div class="small">
                        @foreach($categories as $category)
                        <div class="mb-2">
                            <span class="badge me-2" style="background-color: {{ $category->color_code }}">
                                {{ $category->name }}
                            </span>
                            <br>
                            <small class="text-muted">{{ $category->description }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recent Additions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="bi bi-clock-history"></i> Fauna Terbaru
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        @php
                            $recentFauna = \App\Models\PriorityFauna::with('category')
                                ->orderBy('created_at', 'desc')
                                ->limit(5)
                                ->get();
                        @endphp
                        
                        @forelse($recentFauna as $fauna)
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge me-2" style="background-color: {{ $fauna->category->color_code }}; font-size: 0.6em;">
                                {{ $fauna->category->name }}
                            </span>
                            <small>{{ $fauna->display_name }}</small>
                        </div>
                        @empty
                        <p class="text-muted text-center">Belum ada fauna</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Inline Script -->
<script>
// console.log('Inline script loaded at:', new Date()); // Debug only

/**
 * DEBUG MODE ACTIVATION:
 * To enable test buttons and debug logging:
 * 1. Change "display: none;" to "display: block;" on test button containers
 * 2. Uncomment console.log statements marked with "// Debug only"
 * 3. Test buttons include: Test JavaScript, Test Console, Test Inline Function, Test Perena, Test Form Data
 * 4. Debug buttons in input group: gear (⚙️) and bug (🐛) icons
 */

// Simple test functions that should work immediately
function testBasicFunction() {
    alert('Basic function works!');
    console.log('testBasicFunction called successfully');
}

function testSearchDisplay() {
    console.log('testSearchDisplay called');
    
    // Test data showing taxonomic hierarchy sorting: Genus -> Species -> Subspecies
    const testData = [
        // This will be sorted to show: Panthera (GENUS) first, then Panthera leo (SPECIES), then Panthera leo persica (SUBSPECIES)
        {
            id: 12345,
            name: "Panthera leo persica",
            scientific_name: "Panthera leo persica",
            common_name: "Asiatic Lion",
            display_name: "Asiatic Lion (Panthera leo persica)",
            iucn_status: "EN",
            protection_status: "Dilindungi",
            rank: "SUBSPECIES"
        },
        {
            id: 67890,
            name: "Panthera",
            scientific_name: "Panthera",
            common_name: "Big Cats",
            display_name: "Big Cats (Panthera)",
            iucn_status: null,
            protection_status: "Dilindungi",
            rank: "GENUS"
        },
        {
            id: 11111,
            name: "Panthera leo",
            scientific_name: "Panthera leo",
            common_name: "Lion",
            display_name: "Lion (Panthera leo)",
            iucn_status: "VU",
            protection_status: "Dilindungi",
            rank: "SPECIES"
        },
        {
            id: 137241,
            name: "Tachyspiza badia",
            scientific_name: "Tachyspiza badia",
            common_name: "Elang-alap shikra",
            display_name: "Elang-alap shikra (Tachyspiza badia)",
            iucn_status: null,
            protection_status: "Dilindungi",
            rank: "SPECIES"
        }
    ];
    
    displaySearchResultsGlobal(testData);
    alert('Test results displayed with Tachyspiza badia!');
}

function testDebugInfo() {
    const info = {
        'Current URL': window.location.href,
        'User Agent': navigator.userAgent,
        'Timestamp': new Date().toISOString()
    };
    
    console.log('=== DEBUG INFO ===', info);
    alert('Debug info logged to console (F12)');
}

function testPerenaSearch() {
    console.log('Testing perena search...');
    
    const url = '{{ route("admin.priority-fauna.api.taxa-suggestions") }}?q=perena&limit=10';
    console.log('Perena search URL:', url);
    
    fetch(url)
        .then(response => {
            console.log('Perena search response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Perena search response:', data);
            
            if (data.success && data.data && data.data.length > 0) {
                displaySearchResultsGlobal(data.data);
                alert(`Found ${data.data.length} results for "perena"`);
            } else {
                alert('No results found for "perena" or API error');
                console.log('No results or error:', data);
            }
        })
        .catch(error => {
            console.error('Perena search error:', error);
            alert('Error searching for "perena": ' + error.message);
        });
}

function testFormData() {
    console.log('=== FORM DATA TEST ===');
    
    // Check all hidden fields
    const formData = {
        taxa_id: document.getElementById('taxa_id').value,
        taxa_name: document.getElementById('taxa_name').value,
        scientific_name: document.getElementById('scientific_name').value,
        common_name: document.getElementById('common_name').value,
        iucn_status: document.getElementById('iucn_status').value,
        protection_status: document.getElementById('protection_status').value,
        taxa_rank: document.getElementById('taxa_rank').value,
        taxa_kingdom: document.getElementById('taxa_kingdom').value,
        taxa_data_json: document.getElementById('taxa_data_json').value,
        category_id: document.getElementById('category_id').value
    };
    
    console.log('Current form data:', formData);
    
    // Check if required fields are filled
    const missingFields = [];
    if (!formData.taxa_id) missingFields.push('taxa_id');
    if (!formData.taxa_name) missingFields.push('taxa_name');
    if (!formData.category_id) missingFields.push('category_id');
    
    if (missingFields.length > 0) {
        alert('Missing required fields: ' + missingFields.join(', '));
    } else {
        alert('Form data looks good! Check console for details.');
    }
    
    return formData;
}
</script>

@section('scripts')
<script>
// Global test functions
function testSimpleFunction() {
    alert('JavaScript berfungsi! Tombol dapat diklik.');
    console.log('Test function called successfully');
}

function testSearchFunction() {
    console.log('Test search function called');
    const testData = [
        {
            id: 12345,
            name: 'Varanus komodoensis',
            common_name: 'Komodo Dragon',
            display_name: 'Komodo Dragon (Varanus komodoensis)',
            iucn_status: 'VU',
            protection_status: 'Dilindungi'
        },
        {
            id: 67890,
            name: 'Panthera tigris',
            common_name: 'Tiger',
            display_name: 'Tiger (Panthera tigris)',
            iucn_status: 'EN',
            protection_status: 'Dilindungi'
        }
    ];
    
    // Use global function that doesn't depend on DOM loading
    displaySearchResultsGlobal(testData);
}

function testDebugFunction() {
    console.log('Debug function called');
    const debugInfo = {
        'Current URL': window.location.href,
        'Taxa Search URL': '{{ route("admin.priority-fauna.api.taxa-suggestions") }}',
        'Test URL': '{{ route("admin.priority-fauna.api.test") }}',
        'Timestamp': new Date().toISOString()
    };
    
    console.log('=== DEBUG INFO ===', debugInfo);
    alert('Debug info logged to console. Check Developer Tools > Console (F12)');
}

// Global variable to store functions after DOM is loaded
let globalFunctions = {};

function displaySearchResultsGlobal(results) {
    console.log('Global displaySearchResults called with:', results);
    
    const resultsList = document.getElementById('resultsList');
    const searchResults = document.getElementById('searchResults');
    
    if (!resultsList || !searchResults) {
        console.error('Required elements not found for global display');
        return;
    }
    
    resultsList.innerHTML = '';
    
    if (!results || results.length === 0) {
        resultsList.innerHTML = `
            <div class="list-group-item text-center text-muted">
                <i class="bi bi-search display-6 d-block mb-2"></i>
                <strong>Tidak ada hasil ditemukan</strong>
            </div>
        `;
        searchResults.style.display = 'block';
        return;
    }
    
    // Function to get rank priority (older/higher ranks first)
    const getRankPriority = (rank) => {
        const r = (rank || '').toUpperCase();
        switch (r) {
            case 'FAMILY': return 0;
            case 'GENUS': return 1;
            case 'SPECIES': return 2;
            case 'SUBSPECIES':
            case 'VARIETY':
            case 'FORM':
            case 'FORMA': return 3;
            default: return 4;
        }
    };
    
    // Sort results by taxonomic hierarchy: Genus -> Species -> Subspecies
    const sortedResults = [...results].sort((a, b) => {
        const rankA = a.rank || a.taxon_rank || '';
        const rankB = b.rank || b.taxon_rank || '';
        
        const priorityA = getRankPriority(rankA);
        const priorityB = getRankPriority(rankB);
        
        // First sort by rank priority
        if (priorityA !== priorityB) {
            return priorityA - priorityB;
        }
        
        // Then sort alphabetically by name within same rank
        const nameA = (a.name || a.scientific_name || '').toLowerCase();
        const nameB = (b.name || b.scientific_name || '').toLowerCase();
        return nameA.localeCompare(nameB);
    });
    
    sortedResults.forEach((taxa, index) => {
        const item = document.createElement('div');
        item.className = 'list-group-item list-group-item-action';
        item.style.cursor = 'pointer';
        
        const displayName = taxa.display_name || taxa.name || taxa.common_name || 'Unknown Taxa';
        const scientificName = taxa.name || taxa.scientific_name || '';
        const iucnStatus = taxa.iucn_status || '';
        const protectionStatus = taxa.protection_status || '';
        const rank = taxa.rank || taxa.taxon_rank || '';
        
        // Function to get rank badge color
        const getRankColor = (rank) => {
            const rankLower = rank.toLowerCase();
            if (rankLower === 'species') return 'primary';
            if (rankLower === 'subspecies') return 'info';
            if (rankLower === 'genus') return 'secondary';
            if (rankLower === 'family') return 'dark';
            if (rankLower === 'order') return 'warning';
            if (rankLower === 'class') return 'danger';
            return 'light';
        };
        
        item.innerHTML = `
            <div class="d-flex w-100 justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center mb-1">
                        <h6 class="mb-0 me-2">${displayName}</h6>
                        ${rank ? '<span class="badge bg-' + getRankColor(rank) + ' badge-sm">' + rank + '</span>' : ''}
                    </div>
                    ${scientificName && scientificName !== displayName ? '<small class="text-muted fst-italic">' + scientificName + '</small>' : ''}
                    ${taxa.id ? '<br><small class="text-muted">ID: ' + taxa.id + '</small>' : ''}
                </div>
                <div class="text-end ms-2">
                    ${iucnStatus ? '<span class="badge bg-warning mb-1">' + iucnStatus + '</span><br>' : ''}
                    ${protectionStatus ? '<span class="badge bg-success">' + protectionStatus + '</span>' : ''}
                </div>
            </div>
        `;
        
        item.addEventListener('click', () => {
            // Check if we're in the main DOM context or global context
            if (typeof selectTaxa === 'function') {
                selectTaxa(taxa);
            } else {
                // Fallback for global context
                // console.log('Taxa selected:', taxa); // Debug only
                alert('Taxa dipilih: ' + displayName + ' (ID: ' + taxa.id + ')');
            }
        });
        
        resultsList.appendChild(item);
    });
    
    searchResults.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', function() {
    // console.log('DOM Content Loaded - Priority Fauna Create Page'); // Debug only
    
    const taxaSearch = document.getElementById('taxa_search');
    const searchResults = document.getElementById('searchResults');
    const resultsList = document.getElementById('resultsList');
    const selectedTaxaInfo = document.getElementById('selectedTaxaInfo');
    const selectedTaxaDetails = document.getElementById('selectedTaxaDetails');
    const submitBtn = document.getElementById('submitBtn');
    const clearSearch = document.getElementById('clearSearch');
    const testSearch = document.getElementById('testSearch');
    const debugBtn = document.getElementById('debugBtn');
    
    // Debug: Check if elements exist (Debug only)
    // console.log('Elements found:', { taxaSearch: !!taxaSearch, searchResults: !!searchResults, resultsList: !!resultsList });
    
    if (!taxaSearch) {
        // console.error('taxa_search element not found!'); // Debug only
        return;
    }
    
    let searchTimeout;
    let selectedTaxa = null;

    // Search functionality
    if (taxaSearch) {
        taxaSearch.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                hideSearchResults();
                return;
            }

            searchTimeout = setTimeout(() => {
                searchTaxa(query);
            }, 500);
        });
    }

    // Clear search
    if (clearSearch) {
        clearSearch.addEventListener('click', function() {
            taxaSearch.value = '';
            hideSearchResults();
            clearSelectedTaxa();
        });
    }

    // Test search functionality (Hidden for production)
    if (testSearch) {
        testSearch.addEventListener('click', function() {
            // Test data for debugging
            const testData = [
                {
                    id: 12345,
                    name: 'Varanus komodoensis',
                    scientific_name: 'Varanus komodoensis',
                    common_name: 'Komodo Dragon',
                    display_name: 'Komodo Dragon (Varanus komodoensis)',
                    iucn_status: 'VU',
                    protection_status: 'Dilindungi',
                    rank: 'SPECIES'
                }
            ];
            displaySearchResults(testData);
        });
    }

    // Debug functionality (Hidden for production)
    if (debugBtn) {
        debugBtn.addEventListener('click', function() {
            const debugInfo = {
                'Current URL': window.location.href,
                'Timestamp': new Date().toISOString(),
                'CSRF Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 'Not found',
                'User Agent': navigator.userAgent
            };
            console.log('=== DEBUG INFO ===', debugInfo);
            alert('Debug info logged to console. Check Developer Tools > Console (F12)');
        });
    }

    function searchTaxa(query) {
        showLoading();
        
        const baseUrl = '{{ route("admin.priority-fauna.api.taxa-suggestions") }}';
        const url = baseUrl + '?q=' + encodeURIComponent(query) + '&limit=10';
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                hideLoading();
                
                if (data.success && data.data && data.data.length > 0) {
                    displaySearchResults(data.data);
                } else {
                    displaySearchResults([]);
                }
            })
            .catch(error => {
                // console.error('Search error:', error); // Debug only
                hideLoading();
                displaySearchResults([]);
            });
    }

    function showLoading() {
        resultsList.innerHTML = '<div class="list-group-item text-center"><i class="bi bi-hourglass-split"></i> Mencari...</div>';
        searchResults.style.display = 'block';
    }

    function hideLoading() {
        // Loading will be replaced by search results or error message
        // This function exists for consistency but actual hiding is handled by displaySearchResults
    }

    function displaySearchResults(results) {
        // console.log('Displaying results:', results); // Debug only
        resultsList.innerHTML = '';
        
        if (!results || results.length === 0) {
            showNoResults();
            return;
        }
        
        // Function to get rank priority (older/higher ranks first)
        const getRankPriority = (rank) => {
            const r = (rank || '').toUpperCase();
            switch (r) {
                case 'FAMILY': return 0;
                case 'GENUS': return 1;
                case 'SPECIES': return 2;
                case 'SUBSPECIES':
                case 'VARIETY':
                case 'FORM':
                case 'FORMA': return 3;
                default: return 4;
            }
        };
        
        // Sort results by taxonomic hierarchy: Genus -> Species -> Subspecies
        const sortedResults = [...results].sort((a, b) => {
            const rankA = a.rank || a.taxon_rank || '';
            const rankB = b.rank || b.taxon_rank || '';
            
            const priorityA = getRankPriority(rankA);
            const priorityB = getRankPriority(rankB);
            
            // First sort by rank priority
            if (priorityA !== priorityB) {
                return priorityA - priorityB;
            }
            
            // Then sort alphabetically by name within same rank
            const nameA = (a.name || a.scientific_name || '').toLowerCase();
            const nameB = (b.name || b.scientific_name || '').toLowerCase();
            return nameA.localeCompare(nameB);
        });
        
        sortedResults.forEach((taxa, index) => {
            const item = document.createElement('div');
            item.className = 'list-group-item list-group-item-action';
            item.style.cursor = 'pointer';
            
            // Pastikan data tidak undefined
            const displayName = taxa.display_name || taxa.name || taxa.common_name || 'Unknown Taxa';
            const scientificName = taxa.name || taxa.scientific_name || '';
            const iucnStatus = taxa.iucn_status || '';
            const protectionStatus = taxa.protection_status || '';
            const rank = taxa.rank || taxa.taxon_rank || '';
            
            // Function to get rank badge color
            const getRankColor = (rank) => {
                const rankLower = rank.toLowerCase();
                if (rankLower === 'species') return 'primary';
                if (rankLower === 'subspecies') return 'info';
                if (rankLower === 'genus') return 'secondary';
                if (rankLower === 'family') return 'dark';
                if (rankLower === 'order') return 'warning';
                if (rankLower === 'class') return 'danger';
                return 'light';
            };
            
            item.innerHTML = `
                <div class="d-flex w-100 justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-1">
                            <h6 class="mb-0 me-2">${displayName}</h6>
                            ${rank ? '<span class="badge bg-' + getRankColor(rank) + ' badge-sm">' + rank + '</span>' : ''}
                        </div>
                        ${scientificName && scientificName !== displayName ? '<small class="text-muted fst-italic">' + scientificName + '</small>' : ''}
                        ${taxa.id ? '<br><small class="text-muted">ID: ' + taxa.id + '</small>' : ''}
                    </div>
                    <div class="text-end ms-2">
                        ${iucnStatus ? '<span class="badge bg-warning mb-1">' + iucnStatus + '</span><br>' : ''}
                        ${protectionStatus ? '<span class="badge bg-success">' + protectionStatus + '</span>' : ''}
                    </div>
                </div>
            `;
            
            item.addEventListener('click', () => selectTaxa(taxa));
            resultsList.appendChild(item);
        });
        
        searchResults.style.display = 'block';
    }

    function showNoResults() {
        resultsList.innerHTML = `
            <div class="list-group-item text-center text-muted py-4">
                <i class="bi bi-search display-6 d-block mb-2"></i>
                <strong>Tidak ada hasil ditemukan</strong>
                <br><small>Coba gunakan kata kunci yang berbeda</small>
            </div>
        `;
        searchResults.style.display = 'block';
    }

    function showError() {
        resultsList.innerHTML = `
            <div class="list-group-item text-center text-danger py-4">
                <i class="bi bi-exclamation-triangle display-6 d-block mb-2"></i>
                <strong>Terjadi kesalahan saat mencari</strong>
                <br><small>Periksa koneksi internet atau coba lagi nanti</small>
            </div>
        `;
        searchResults.style.display = 'block';
    }

    function hideSearchResults() {
        searchResults.style.display = 'none';
    }

    function selectTaxa(taxa) {
        selectedTaxa = taxa;
        
        // console.log('Selecting taxa:', taxa); // Debug only
        
        // Fill basic hidden fields
        document.getElementById('taxa_id').value = taxa.id || '';
        document.getElementById('taxa_name').value = taxa.display_name || taxa.name || '';
        document.getElementById('scientific_name').value = taxa.scientific_name || taxa.name || '';
        document.getElementById('common_name').value = taxa.common_name || '';
        
        // Fill additional hidden fields
        document.getElementById('iucn_status').value = taxa.iucn_status || '';
        document.getElementById('protection_status').value = taxa.protection_status || '';
        document.getElementById('taxa_rank').value = taxa.rank || '';
        document.getElementById('taxa_kingdom').value = taxa.kingdom || '';
        document.getElementById('taxa_phylum').value = taxa.phylum || '';
        document.getElementById('taxa_class').value = taxa.class || '';
        document.getElementById('taxa_order').value = taxa.order || '';
        document.getElementById('taxa_family').value = taxa.family || '';
        document.getElementById('taxa_genus').value = taxa.genus || '';
        document.getElementById('taxa_species').value = taxa.species || '';
        
        // Store complete taxa data as JSON
        document.getElementById('taxa_data_json').value = JSON.stringify(taxa);
        
        // console.log('Hidden fields filled:', { taxa_id: taxa.id, protection_status: taxa.protection_status, iucn_status: taxa.iucn_status }); // Debug only
        
        // Show selected taxa info
        selectedTaxaDetails.innerHTML = `
            <div class="row">
                <div class="col-md-8">
                    <strong>${taxa.display_name}</strong>
                    ${taxa.scientific_name ? '<br><small class="text-muted fst-italic">' + taxa.scientific_name + '</small>' : ''}
                    <br><small class="text-muted">Taxa ID: ${taxa.id}</small>
                </div>
                <div class="col-md-4 text-end">
                    ${taxa.iucn_status ? '<span class="badge bg-warning">' + taxa.iucn_status + '</span><br>' : ''}
                    ${taxa.protection_status ? '<span class="badge bg-success">' + taxa.protection_status + '</span>' : ''}
                </div>
            </div>
        `;
        
        selectedTaxaInfo.style.display = 'block';
        hideSearchResults();
        
        // Clear search input
        taxaSearch.value = taxa.display_name;
        
        // Enable submit button
        submitBtn.disabled = false;
    }

    function clearSelectedTaxa() {
        selectedTaxa = null;
        selectedTaxaInfo.style.display = 'none';
        
        // Clear hidden fields
        document.getElementById('taxa_id').value = '';
        document.getElementById('taxa_name').value = '';
        document.getElementById('scientific_name').value = '';
        document.getElementById('common_name').value = '';
        
        // Disable submit button
        submitBtn.disabled = true;
    }

    // Form validation
    document.getElementById('faunaForm').addEventListener('submit', function(e) {
        if (!selectedTaxa || !document.getElementById('taxa_id').value) {
            e.preventDefault();
            alert('Silakan pilih taksa terlebih dahulu!');
            return false;
        }
        
        if (!document.getElementById('category_id').value) {
            e.preventDefault();
            alert('Silakan pilih kategori prioritas!');
            return false;
        }
    });
});
</script>
@endsection
