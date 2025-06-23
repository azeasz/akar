<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Checklist;
use App\Models\ChecklistFauna;
use App\Models\ChecklistImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChecklistController extends Controller
{
    /**
     * Menampilkan daftar checklist
     */
    public function index(Request $request)
    {
        // Dapatkan parameter filter
        $filters = [
            'search' => $request->input('search'),
            'type' => $request->input('type'),
            'status' => $request->input('status'),
            'user_id' => $request->input('user_id'),
            'is_completed' => $request->input('is_completed'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'category_id' => $request->input('category_id'), // Filter tambahan untuk kompatibilitas
        ];
        
        // Query dasar
        $query = Checklist::with(['user', 'faunas']);
        
        // Filter berdasarkan tipe
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        // Filter berdasarkan category_id (untuk kompatibilitas)
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        
        // Filter berdasarkan status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        // Filter berdasarkan user
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        // Filter berdasarkan kelengkapan data
        if (isset($filters['is_completed']) && $filters['is_completed'] !== '') {
            $isCompleted = $filters['is_completed'] === 'true' || $filters['is_completed'] === '1';
            
            // Cek baik is_completed maupun confirmed (untuk kompatibilitas)
            $query->where(function($q) use ($isCompleted) {
                $q->where('is_completed', $isCompleted)
                  ->orWhere('confirmed', $isCompleted);
            });
        }
        
        // Filter berdasarkan tanggal
        if (!empty($filters['date_from'])) {
            $query->whereDate('tanggal', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->whereDate('tanggal', '<=', $filters['date_to']);
        }
        
        // Filter pencarian
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_lokasi', 'like', $searchTerm)
                  ->orWhere('name', 'like', $searchTerm) // Untuk kompatibilitas
                  ->orWhere('pemilik', 'like', $searchTerm)
                  ->orWhere('nama_pemilik', 'like', $searchTerm) // Untuk kompatibilitas
                  ->orWhereHas('user', function($q) use ($searchTerm) {
                      $q->where('name', 'like', $searchTerm)
                        ->orWhere('email', 'like', $searchTerm);
                  });
            });
        }
        
        // Urutkan berdasarkan tanggal terbaru
        $query->latest('tanggal');
        
        // Paginate hasil
        $checklists = $query->paginate(10)->withQueryString();
        
        // Dapatkan data untuk filter dropdown
        $users = User::orderBy('name')->get(['id', 'name']);
        $types = Checklist::distinct()->pluck('type')->filter()->sort();
        
        // Konversi data untuk tampilan yang konsisten
        $checklists->getCollection()->transform(function($checklist) {
            // Tambahkan property untuk tampilan
            $checklist->completion_status = $checklist->is_completed || $checklist->confirmed ? 'Selesai' : 'Belum Selesai';
            $checklist->type_text = ucfirst($checklist->type);
            $checklist->category_text = $this->getCategoryText($checklist->category_id);
            $checklist->pemilik_display = $checklist->nama_pemilik ?? $checklist->pemilik ?? 'Tidak Ada';
            
            return $checklist;
        });
        
        // Log aktivitas
        activity_log('view', 'Admin melihat daftar checklist');
        
        return view('admin.checklists.index', compact('checklists', 'filters', 'users', 'types'));
    }
    
    /**
     * Menampilkan form untuk membuat checklist baru
     */
    public function create()
    {
        $users = User::select('id', 'name')->orderBy('name')->get();
        return view('admin.checklists.create', compact('users'));
    }
    
    /**
     * Menyimpan checklist baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string',
            'status' => 'required|in:draft,published',
            'tanggal' => 'required|date',
            'nama_lokasi' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'pemilik' => 'nullable|string',
            'catatan' => 'nullable|string',
            'is_completed' => 'boolean',
            
            // Fauna data
            'faunas.*.nama_spesies' => 'required|string',
            'faunas.*.jumlah' => 'required|integer|min:1',
            'faunas.*.gender' => 'nullable|string',
            'faunas.*.cincin' => 'nullable|boolean',
            'faunas.*.tagging' => 'nullable|boolean',
            'faunas.*.catatan' => 'nullable|string',
            'faunas.*.status_buruan' => 'nullable|string|in:hidup,mati',
            'faunas.*.alat_buru' => 'nullable|string',
            
            // Images
            'images.*' => 'nullable|image|max:5120', // 5MB
            
            // Kolom tambahan untuk kompatibilitas
            'category_id' => 'nullable|integer',
            'name' => 'nullable|string',
            'nama_event' => 'nullable|string',
            'nama_arena' => 'nullable|string',
            'total_hunter' => 'nullable|integer',
            'teknik_berburu' => 'nullable|string',
            'nama_pemilik' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Siapkan data untuk checklist
            $checklistData = [
                'user_id' => $validated['user_id'],
                'type' => $validated['type'],
                'status' => $validated['status'],
                'tanggal' => $validated['tanggal'],
                'nama_lokasi' => $validated['nama_lokasi'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'pemilik' => $validated['pemilik'] ?? null,
                'catatan' => $validated['catatan'] ?? null,
                'is_completed' => $validated['is_completed'] ?? false,
            ];
            
            // Tambahkan data untuk kompatibilitas dengan struktur lama
            $checklistData['name'] = $validated['name'] ?? $validated['nama_lokasi'];
            $checklistData['category_id'] = $validated['category_id'] ?? $this->getCategoryIdFromType($validated['type']);
            $checklistData['nama_event'] = $validated['nama_event'] ?? null;
            $checklistData['nama_arena'] = $validated['nama_arena'] ?? null;
            $checklistData['total_hunter'] = $validated['total_hunter'] ?? 0;
            $checklistData['teknik_berburu'] = $validated['teknik_berburu'] ?? null;
            $checklistData['nama_pemilik'] = $validated['nama_pemilik'] ?? $validated['pemilik'] ?? null;
            $checklistData['confirmed'] = $validated['is_completed'] ?? false;
            
            // Create checklist
            $checklist = Checklist::create($checklistData);
            
            // Create fauna records
            if (!empty($validated['faunas'])) {
                foreach ($validated['faunas'] as $faunaData) {
                    // Konversi gender dari string ke kode untuk kompatibilitas
                    $genderCode = $this->getGenderCode($faunaData['gender'] ?? null);
                    
                    // Konversi status_buruan dari enum ke kode untuk kompatibilitas
                    $kondisiCode = $this->getKondisiCode($faunaData['status_buruan'] ?? null);
                    
                    ChecklistFauna::create([
                        'checklist_id' => $checklist->id,
                        'nama_spesies' => $faunaData['nama_spesies'],
                        'jumlah' => $faunaData['jumlah'],
                        'gender' => $faunaData['gender'] ?? null,
                        'cincin' => $faunaData['cincin'] ?? false,
                        'tagging' => $faunaData['tagging'] ?? false,
                        'catatan' => $faunaData['catatan'] ?? null,
                        'status_buruan' => $faunaData['status_buruan'] ?? null,
                        'alat_buru' => $faunaData['alat_buru'] ?? null,
                        
                        // Kolom untuk kompatibilitas
                        'total' => $faunaData['jumlah'], // Untuk kompatibilitas
                        'kondisi' => $kondisiCode,
                        'fauna_id' => $faunaData['fauna_id'] ?? null,
                    ]);
                }
            }
            
            // Upload images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('checklist_images', 'public');
                    
                    ChecklistImage::create([
                        'checklist_id' => $checklist->id,
                        'image_path' => $path,
                    ]);
                }
            }
            
            DB::commit();
            
            // Log aktivitas
            activity_log('create', 'Admin membuat checklist baru di lokasi ' . $checklist->nama_lokasi);
            
            return redirect()->route('admin.checklists.index')
                ->with('success', 'Checklist berhasil dibuat!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Helper method untuk mengkonversi tipe ke category_id
     */
    private function getCategoryIdFromType($type)
    {
        $mapping = [
            'perburuan' => 1,
            'lomba' => 2,
            'perdagangan' => 3,
            'pemeliharaan' => 4,
            'penangkaran' => 5,
            'pemeliharaan & penangkaran' => 6,
            'pemeliharaan dan penangkaran' => 6
        ];
        
        return $mapping[strtolower($type)] ?? 6; // default ke pemeliharaan & penangkaran
    }
    
    /**
     * Helper method untuk mengkonversi tipe dari category_id
     */
    private function getTypeFromCategoryId($categoryId)
    {
        $mapping = [
            1 => 'perburuan',
            2 => 'lomba',
            3 => 'perdagangan',
            4 => 'pemeliharaan',
            5 => 'penangkaran',
            6 => 'pemeliharaan & penangkaran'
        ];
        
        return $mapping[$categoryId] ?? 'pemeliharaan & penangkaran'; // default ke pemeliharaan & penangkaran
    }
    
    /**
     * Helper method untuk mengkonversi gender string ke kode
     */
    private function getGenderCode($gender)
    {
        if (empty($gender)) {
            return null;
        }
        
        $gender = strtolower($gender);
        
        $mapping = [
            'jantan' => 1,
            'betina' => 2,
            'tidak diketahui' => 0,
            'male' => 1,
            'female' => 2,
            'unknown' => 0
        ];
        
        return $mapping[$gender] ?? 0; // default ke tidak diketahui
    }
    
    /**
     * Helper method untuk mengkonversi gender kode ke string
     */
    private function getGenderString($code)
    {
        $mapping = [
            1 => 'Jantan',
            2 => 'Betina',
            0 => 'Tidak Diketahui',
            null => 'Tidak Diketahui'
        ];
        
        return $mapping[$code] ?? 'Tidak Diketahui';
    }
    
    /**
     * Helper method untuk mengkonversi status buruan ke kode kondisi
     */
    private function getKondisiCode($statusBuruan)
    {
        if (empty($statusBuruan)) {
            return null;
        }
        
        $statusBuruan = strtolower($statusBuruan);
        
        $mapping = [
            'hidup' => 1,
            'mati' => 2,
            'lainnya' => 3,
            'live' => 1,
            'dead' => 2,
            'other' => 3
        ];
        
        return $mapping[$statusBuruan] ?? 3; // default ke lainnya
    }
    
    /**
     * Helper method untuk mengkonversi kode kondisi ke status buruan
     */
    private function getStatusBuruanString($kondisiCode)
    {
        $mapping = [
            1 => 'Hidup',
            2 => 'Mati',
            3 => 'Lainnya',
            null => 'Tidak Diketahui'
        ];
        
        return $mapping[$kondisiCode] ?? 'Tidak Diketahui';
    }
    
    /**
     * Menampilkan detail checklist
     */
    public function show(Checklist $checklist)
    {
        // Ambil data checklist dengan relasi
        $checklist->load(['user', 'faunas', 'images']);
        
        // Konversi data untuk tampilan yang konsisten
        $viewData = [
            'checklist' => $checklist,
            'user' => $checklist->user,
            'faunas' => $checklist->faunas->map(function($fauna) {
                // Konversi kode ke string jika diperlukan
                if (is_numeric($fauna->gender)) {
                    $fauna->gender_text = $this->getGenderString($fauna->gender);
                } else {
                    $fauna->gender_text = $fauna->gender;
                }
                
                // Konversi kode kondisi ke status buruan jika diperlukan
                if (is_numeric($fauna->kondisi)) {
                    $fauna->status_text = $this->getStatusBuruanString($fauna->kondisi);
                } else {
                    $fauna->status_text = $fauna->status_buruan;
                }
                
                return $fauna;
            }),
            'images' => $checklist->images,
            'type_text' => ucfirst($checklist->type),
            'category_text' => $this->getCategoryText($checklist->category_id),
            'completion_status' => $checklist->is_completed || $checklist->confirmed ? 'Selesai' : 'Belum Selesai',
            'pemilik_display' => $checklist->nama_pemilik ?? $checklist->pemilik ?? 'Tidak Ada',
        ];
        
        // Log aktivitas
        activity_log('view', 'Admin melihat detail checklist di lokasi ' . $checklist->nama_lokasi);
        
        return view('admin.checklists.show', $viewData);
    }
    
    /**
     * Helper method untuk mendapatkan teks kategori dari category_id
     */
    private function getCategoryText($categoryId)
    {
        $mapping = [
            1 => 'Perburuan',
            2 => 'Lomba',
            3 => 'Perdagangan',
            4 => 'Pemeliharaan',
            5 => 'Penangkaran',
            6 => 'Pemeliharaan & Penangkaran'
        ];
        
        return $mapping[$categoryId] ?? 'Tidak Diketahui';
    }
    
    /**
     * Menampilkan form untuk edit checklist
     */
    public function edit(Checklist $checklist)
    {
        $checklist->load(['faunas', 'images']);
        $users = User::select('id', 'name')->orderBy('name')->get();
        return view('admin.checklists.edit', compact('checklist', 'users'));
    }
    
    /**
     * Mengupdate data checklist
     */
    public function update(Request $request, Checklist $checklist)
    {
        // Log request info
        \Log::info('Update checklist request', [
            'checklist_id' => $checklist->id,
            'request_path' => $request->path(),
            'request_method' => $request->method(),
            'request_url' => $request->url(),
            'is_ajax' => $request->ajax(),
            'all_inputs' => $request->all()
        ]);

        // Pastikan ini adalah request update yang valid
        if ($request->method() !== 'PUT' && $request->method() !== 'PATCH') {
            \Log::warning('Invalid update method detected', [
                'method' => $request->method(),
                'path' => $request->path()
            ]);
            return redirect()->back()->with('error', 'Metode request tidak valid');
        }
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string',
            'status' => 'required|in:draft,published',
            'tanggal' => 'required|date',
            'nama_lokasi' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'pemilik' => 'nullable|string',
            'catatan' => 'nullable|string',
            'is_completed' => 'boolean',
            
            // Fauna data
            'faunas.*.id' => 'nullable|exists:checklist_faunas,id',
            'faunas.*.nama_spesies' => 'required|string',
            'faunas.*.jumlah' => 'required|integer|min:1',
            'faunas.*.gender' => 'nullable|string',
            'faunas.*.cincin' => 'nullable|boolean',
            'faunas.*.tagging' => 'nullable|boolean',
            'faunas.*.catatan' => 'nullable|string',
            'faunas.*.status_buruan' => 'nullable|string|in:hidup,mati',
            'faunas.*.alat_buru' => 'nullable|string',
            
            // Images
            'images.*' => 'nullable|image|max:5120', // 5MB
            'removed_images' => 'nullable|array',
            'removed_images.*' => 'nullable|exists:checklist_images,id',
            
            // Kolom tambahan untuk kompatibilitas
            'category_id' => 'nullable|integer',
            'name' => 'nullable|string',
            'nama_event' => 'nullable|string',
            'nama_arena' => 'nullable|string',
            'total_hunter' => 'nullable|integer',
            'teknik_berburu' => 'nullable|string',
            'nama_pemilik' => 'nullable|string',
        ]);
        
        // Debug logging
        \Log::info('Updating checklist', [
            'checklist_id' => $checklist->id,
            'is_completed_in_request' => isset($validated['is_completed']),
            'is_completed_value' => $validated['is_completed'] ?? 'not set',
            'has_faunas' => isset($validated['faunas']),
            'fauna_count' => isset($validated['faunas']) ? count($validated['faunas']) : 0
        ]);
        
        DB::beginTransaction();
        
        try {
            // Siapkan data untuk update checklist
            $checklistData = [
                'user_id' => $validated['user_id'],
                'type' => $validated['type'],
                'status' => $validated['status'],
                'tanggal' => $validated['tanggal'],
                'nama_lokasi' => $validated['nama_lokasi'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'pemilik' => $validated['pemilik'] ?? null,
                'catatan' => $validated['catatan'] ?? null,
                'is_completed' => isset($validated['is_completed']) ? $validated['is_completed'] : false,
            ];
            
            // Tambahkan data untuk kompatibilitas dengan struktur lama
            $checklistData['name'] = $validated['name'] ?? $validated['nama_lokasi'];
            $checklistData['category_id'] = $validated['category_id'] ?? $this->getCategoryIdFromType($validated['type']);
            $checklistData['nama_event'] = $validated['nama_event'] ?? null;
            $checklistData['nama_arena'] = $validated['nama_arena'] ?? null;
            $checklistData['total_hunter'] = $validated['total_hunter'] ?? 0;
            $checklistData['teknik_berburu'] = $validated['teknik_berburu'] ?? null;
            $checklistData['nama_pemilik'] = $validated['nama_pemilik'] ?? $validated['pemilik'] ?? null;
            $checklistData['confirmed'] = isset($validated['is_completed']) ? $validated['is_completed'] : false;
            
            // Update checklist
            $checklist->update($checklistData);
            
            // Debug logging after update
            \Log::info('Checklist updated', [
                'checklist_id' => $checklist->id,
                'is_completed_after_update' => $checklist->is_completed
            ]);
            
            // Update existing faunas and create new ones
            $existingIds = [];
            if (isset($validated['faunas']) && is_array($validated['faunas'])) {
                foreach ($validated['faunas'] as $faunaData) {
                    // Konversi gender dari string ke kode untuk kompatibilitas
                    $genderCode = $this->getGenderCode($faunaData['gender'] ?? null);
                    
                    // Konversi status_buruan dari enum ke kode untuk kompatibilitas
                    $kondisiCode = $this->getKondisiCode($faunaData['status_buruan'] ?? null);
                    
                    if (!empty($faunaData['id'])) {
                        // Update existing
                        $fauna = ChecklistFauna::find($faunaData['id']);
                        if ($fauna) {
                            $fauna->update([
                                'nama_spesies' => $faunaData['nama_spesies'],
                                'jumlah' => $faunaData['jumlah'],
                                'gender' => $faunaData['gender'] ?? null,
                                'cincin' => $faunaData['cincin'] ?? false,
                                'tagging' => $faunaData['tagging'] ?? false,
                                'catatan' => $faunaData['catatan'] ?? null,
                                'status_buruan' => $faunaData['status_buruan'] ?? null,
                                'alat_buru' => $faunaData['alat_buru'] ?? null,
                                
                                // Kolom untuk kompatibilitas
                                'total' => $faunaData['jumlah'],
                                'kondisi' => $kondisiCode,
                            ]);
                            $existingIds[] = $fauna->id;
                        }
                    } else {
                        // Create new
                        $fauna = ChecklistFauna::create([
                            'checklist_id' => $checklist->id,
                            'nama_spesies' => $faunaData['nama_spesies'],
                            'jumlah' => $faunaData['jumlah'],
                            'gender' => $faunaData['gender'] ?? null,
                            'cincin' => $faunaData['cincin'] ?? false,
                            'tagging' => $faunaData['tagging'] ?? false,
                            'catatan' => $faunaData['catatan'] ?? null,
                            'status_buruan' => $faunaData['status_buruan'] ?? null,
                            'alat_buru' => $faunaData['alat_buru'] ?? null,
                            
                            // Kolom untuk kompatibilitas
                            'total' => $faunaData['jumlah'],
                            'kondisi' => $kondisiCode,
                            'fauna_id' => $faunaData['fauna_id'] ?? null,
                        ]);
                        $existingIds[] = $fauna->id;
                    }
                }
            }
            
            // Debug logging for fauna deletion
            \Log::info('Fauna deletion check', [
                'checklist_id' => $checklist->id,
                'existing_fauna_ids' => $existingIds,
                'will_delete_faunas' => isset($validated['faunas'])
            ]);
            
            // Delete faunas that were removed - only if we have fauna data in the request
            if (isset($validated['faunas'])) {
                // Dapatkan ID fauna yang akan dihapus dengan pengecekan relasi yang lebih aman
                $faunasToDelete = ChecklistFauna::where('checklist_id', $checklist->id)
                    ->whereNotIn('id', $existingIds);
                
                // Log dulu sebelum dihapus
                \Log::info('Faunas to delete', [
                    'count' => $faunasToDelete->count(),
                    'ids' => $faunasToDelete->pluck('id')->toArray()
                ]);
                
                // Hapus satu per satu untuk menghindari masalah dengan event model
                foreach ($faunasToDelete->get() as $fauna) {
                    // Pastikan fauna ini benar-benar milik checklist ini
                    if ($fauna->checklist_id == $checklist->id) {
                        \Log::info('Deleting fauna', ['fauna_id' => $fauna->id]);
                        // Gunakan forceDelete untuk menghindari masalah dengan soft delete
                        $fauna->delete();
                    } else {
                        \Log::warning('Attempted to delete fauna not belonging to checklist', [
                            'fauna_id' => $fauna->id,
                            'fauna_checklist_id' => $fauna->checklist_id,
                            'current_checklist_id' => $checklist->id
                        ]);
                    }
                }
            }
            
            // Refresh model dari database untuk memastikan tidak terhapus
            $checklist = $checklist->fresh();
            
            // Jika ternyata terhapus (deleted_at tidak null), kembalikan
            if ($checklist->deleted_at !== null) {
                \Log::error('Checklist was soft deleted after update', [
                    'checklist_id' => $checklist->id,
                    'deleted_at' => $checklist->deleted_at
                ]);
                
                // Restore checklist
                $checklist->restore();
                \Log::info('Checklist restored', ['checklist_id' => $checklist->id]);
            }
            
            // Handle removed images
            if (!empty($validated['removed_images'])) {
                $imagesToRemove = ChecklistImage::whereIn('id', $validated['removed_images'])->get();
                
                foreach ($imagesToRemove as $image) {
                    if (Storage::disk('public')->exists($image->image_path)) {
                        Storage::disk('public')->delete($image->image_path);
                    }
                    $image->delete();
                }
            }
            
            // Upload new images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('checklist_images', 'public');
                    
                    ChecklistImage::create([
                        'checklist_id' => $checklist->id,
                        'image_path' => $path,
                    ]);
                }
            }
            
            DB::commit();
            
            // Log aktivitas
            activity_log('update', 'Admin mengupdate checklist di lokasi ' . $checklist->nama_lokasi);
            
            // Debug logging after successful update
            \Log::info('Checklist update completed successfully', [
                'checklist_id' => $checklist->id,
                'is_completed' => $checklist->is_completed,
                'deleted_at' => $checklist->deleted_at
            ]);
            
            return redirect()->route('admin.checklists.index')
                ->with('success', 'Checklist berhasil diupdate!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            // Debug logging for exception
            \Log::error('Error updating checklist', [
                'checklist_id' => $checklist->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Menandai checklist sebagai lengkap
     */
    public function complete(Checklist $checklist)
    {
        try {
            // Update checklist
            $checklist->update([
                'is_completed' => true,
                'confirmed' => true, // Update juga kolom confirmed untuk kompatibilitas
            ]);
            
            // Log aktivitas
            activity_log('update', 'Admin menandai checklist di lokasi ' . $checklist->nama_lokasi . ' sebagai lengkap');
            
            return redirect()->route('admin.checklists.show', $checklist)
                ->with('success', 'Checklist berhasil ditandai sebagai lengkap!');
        } catch (\Exception $e) {
            \Log::error('Error completing checklist', [
                'checklist_id' => $checklist->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Mempublikasikan checklist
     */
    public function publish(Checklist $checklist)
    {
        try {
            // Update checklist
            $checklist->update([
                'status' => 'published',
                'published_at' => now(), // Tambahkan timestamp publikasi jika ada
            ]);
            
            // Log aktivitas
            activity_log('update', 'Admin mempublikasikan checklist di lokasi ' . $checklist->nama_lokasi);
            
            return redirect()->route('admin.checklists.show', $checklist)
                ->with('success', 'Checklist berhasil dipublikasikan!');
        } catch (\Exception $e) {
            \Log::error('Error publishing checklist', [
                'checklist_id' => $checklist->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Menghapus checklist dan semua data terkait
     */
    public function destroy(Checklist $checklist)
    {
        DB::beginTransaction();
        
        try {
            // Simpan informasi untuk log
            $checklistInfo = [
                'id' => $checklist->id,
                'nama_lokasi' => $checklist->nama_lokasi,
                'user_id' => $checklist->user_id,
                'fauna_count' => $checklist->faunas->count(),
                'image_count' => $checklist->images->count()
            ];
            
            // Log sebelum menghapus
            \Log::info('Deleting checklist', $checklistInfo);
            
            // Hapus semua fauna terkait
            foreach ($checklist->faunas as $fauna) {
                \Log::info('Deleting fauna', ['fauna_id' => $fauna->id, 'checklist_id' => $checklist->id]);
                $fauna->delete();
            }
            
            // Hapus semua gambar terkait
            foreach ($checklist->images as $image) {
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
                $image->delete();
            }
            
            // Hapus checklist
            $checklist->delete();
            
            DB::commit();
            
            // Log aktivitas
            activity_log('delete', 'Admin menghapus checklist di lokasi ' . $checklistInfo['nama_lokasi']);
            
            return redirect()->route('admin.checklists.index')
                ->with('success', 'Checklist berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollback();
            
            \Log::error('Error deleting checklist', [
                'checklist_id' => $checklist->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Export data checklist ke CSV
     */
    public function export(Request $request)
    {
        $query = Checklist::with(['user', 'faunas']);
        
        // Apply the same filters as index
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('is_completed')) {
            $query->where('is_completed', $request->is_completed);
        }
        
        if ($request->has('start_date') && $request->start_date) {
            $query->where('tanggal', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->where('tanggal', '<=', $request->end_date);
        }
        
        $sortField = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_order ?? 'desc';
        $checklists = $query->orderBy($sortField, $sortDirection)->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=checklists-export-' . date('Y-m-d') . '.csv',
        ];
        
        $columns = ['ID', 'User', 'Tipe', 'Status', 'Tanggal', 'Lokasi', 'Pemilik', 'Selesai', 'Jumlah Fauna', 'Spesies', 'Catatan'];
        
        $callback = function() use($checklists, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($checklists as $checklist) {
                $faunaSpesies = $checklist->faunas->pluck('nama_spesies')->join(', ');
                
                $row = [
                    $checklist->id,
                    $checklist->user->name,
                    $checklist->type,
                    $checklist->status,
                    $checklist->tanggal->format('Y-m-d'),
                    $checklist->nama_lokasi,
                    $checklist->pemilik ?? '-',
                    $checklist->is_completed ? 'Ya' : 'Tidak',
                    $checklist->faunas->count(),
                    $faunaSpesies,
                    $checklist->catatan ?? '-',
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        };
        
        // Log aktivitas
        activity_log('export', 'Admin mengexport data checklists');
        
        return response()->stream($callback, 200, $headers);
    }
} 