<div class="modal-header">
    <h5 class="modal-title">Pilih Taxa</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="searchTaxaForm" class="mb-3">
        <div class="row g-2">
            <div class="col-md-8">
                <input type="text" class="form-control" id="taxaSearchQuery" name="q" placeholder="Cari nama spesies..." value="{{ $query }}">
            </div>
            <div class="col-md-4">
                <select class="form-select" id="taxaSearchKingdom" name="kingdom">
                    <option value="Animalia" {{ $kingdom == 'Animalia' ? 'selected' : '' }}>Animalia</option>
                    <option value="Plantae" {{ $kingdom == 'Plantae' ? 'selected' : '' }}>Plantae</option>
                    <option value="Fungi" {{ $kingdom == 'Fungi' ? 'selected' : '' }}>Fungi</option>
                </select>
            </div>
        </div>
        <div class="d-grid mt-2">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-search"></i> Cari
            </button>
        </div>
    </form>
    
    <div class="taxa-results">
        @if($results->isEmpty())
            <div class="alert alert-info">
                @if(empty($query))
                    Silakan masukkan kata kunci pencarian.
                @else
                    Tidak ditemukan hasil untuk "{{ $query }}".
                @endif
            </div>
        @else
            <div class="list-group">
                @foreach($results as $taxa)
                    <a href="javascript:void(0)" class="list-group-item list-group-item-action select-taxa" 
                       data-id="{{ $taxa->taxa_id }}" 
                       data-scientific-name="{{ $taxa->scientific_name }}"
                       data-common-name="{{ $taxa->common_name }}"
                       data-rank="{{ $taxa->rank }}"
                       data-kingdom="{{ $taxa->kingdom }}">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 text-primary">{{ $taxa->scientific_name }}</h6>
                            <small>{{ $taxa->rank }}</small>
                        </div>
                        <p class="mb-1">{{ $taxa->common_name ?? 'Tidak ada nama umum' }}</p>
                        <small>{{ $taxa->kingdom }} / {{ $taxa->phylum }} / {{ $taxa->class }}</small>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form pencarian
    document.getElementById('searchTaxaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const query = document.getElementById('taxaSearchQuery').value;
        const kingdom = document.getElementById('taxaSearchKingdom').value;
        
        // Muat hasil pencarian dengan AJAX
        fetch(`{{ route('admin.taxas.select_modal') }}?q=${encodeURIComponent(query)}&kingdom=${encodeURIComponent(kingdom)}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('taxaSelectModal').querySelector('.modal-content').innerHTML = html;
            });
    });
    
    // Klik pada hasil pencarian
    document.querySelectorAll('.select-taxa').forEach(item => {
        item.addEventListener('click', function() {
            const taxaId = this.getAttribute('data-id');
            const scientificName = this.getAttribute('data-scientific-name');
            const commonName = this.getAttribute('data-common-name');
            
            // Isi field input
            document.getElementById('fauna_id').value = taxaId;
            document.getElementById('nama_spesies').value = scientificName;
            
            // Tutup modal
            bootstrap.Modal.getInstance(document.getElementById('taxaSelectModal')).hide();
        });
    });
});
</script> 