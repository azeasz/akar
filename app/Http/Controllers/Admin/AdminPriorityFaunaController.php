<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PriorityFauna;
use App\Models\PriorityFaunaCategory;
use App\Models\PriorityFaunaObservation;
use App\Services\AmaturalistApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminPriorityFaunaController extends Controller
{
    protected $amaturalistService;

    public function __construct(AmaturalistApiService $amaturalistService)
    {
        $this->amaturalistService = $amaturalistService;
    }

    /**
     * Display dashboard with statistics
     */
    public function index()
    {
        $stats = [
            'total_categories' => PriorityFaunaCategory::count(),
            'active_categories' => PriorityFaunaCategory::active()->count(),
            'total_monitored_fauna' => PriorityFauna::monitored()->count(),
            'cr_fauna_count' => PriorityFauna::iucnStatus('CR')->monitored()->count(),
            'protected_fauna_count' => PriorityFauna::protectionStatus('Dilindungi')->monitored()->count(),
            'needs_sync_count' => PriorityFauna::monitored()->get()->filter->needsApiSync()->count(),
            // New observation stats
            'total_observations' => PriorityFaunaObservation::count(),
            'new_observations' => PriorityFaunaObservation::new()->count(),
            'today_observations' => PriorityFaunaObservation::today()->count(),
            'week_observations' => PriorityFaunaObservation::thisWeek()->count(),
        ];

        $recentFauna = PriorityFauna::with('category')
            ->monitored()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $categoriesWithCount = PriorityFaunaCategory::withCount('activePriorityFaunas')
            ->active()
            ->get();

        // Recent priority fauna observations
        $recentObservations = PriorityFaunaObservation::with(['priorityFauna.category', 'user', 'checklist'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.priority-fauna.index', compact('stats', 'recentFauna', 'categoriesWithCount', 'recentObservations'));
    }

    /**
     * Get dashboard data for API (for main admin dashboard)
     */
    public function getDashboardData()
    {
        $stats = [
            'total_monitored_fauna' => PriorityFauna::monitored()->count(),
            'new_observations_today' => PriorityFaunaObservation::today()->count(),
            'new_observations_week' => PriorityFaunaObservation::thisWeek()->count(),
            'pending_reviews' => PriorityFaunaObservation::new()->count(),
        ];

        $recentObservations = PriorityFaunaObservation::with(['priorityFauna.category', 'user'])
            ->new()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($observation) {
                return [
                    'id' => $observation->id,
                    'scientific_name' => $observation->scientific_name,
                    'common_name' => $observation->common_name,
                    'user_name' => $observation->user->name ?? 'Unknown',
                    'location' => $observation->formatted_location,
                    'observed_at' => $observation->observed_at->format('d M Y H:i'),
                    'status' => $observation->status_label,
                    'status_color' => $observation->status_color,
                    'category_color' => $observation->priorityFauna->category->color_code ?? '#6c757d',
                ];
            });

        return response()->json([
            'stats' => $stats,
            'recent_observations' => $recentObservations
        ]);
    }

    /**
     * Display categories management
     */
    public function categories()
    {
        $categories = PriorityFaunaCategory::withCount('priorityFaunas')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('admin.priority-fauna.categories', compact('categories'));
    }

    /**
     * Store new category
     */
    public function storeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:priority_fauna_categories,name',
            'type' => 'required|in:iucn,protection_status,custom',
            'description' => 'nullable|string|max:500',
            'color_code' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            PriorityFaunaCategory::create($request->only([
                'name', 'type', 'description', 'color_code'
            ]));

            return back()->with('success', 'Kategori berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('[AdminPriorityFauna] Failed to create category', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->with('error', 'Gagal menambahkan kategori!');
        }
    }

    /**
     * Update category
     */
    public function updateCategory(Request $request, PriorityFaunaCategory $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:priority_fauna_categories,name,' . $category->id,
            'type' => 'required|in:iucn,protection_status,custom',
            'description' => 'nullable|string|max:500',
            'color_code' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $category->update($request->only([
                'name', 'type', 'description', 'color_code', 'is_active'
            ]));

            return back()->with('success', 'Kategori berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('[AdminPriorityFauna] Failed to update category', [
                'category_id' => $category->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal memperbarui kategori!');
        }
    }

    /**
     * Delete category
     */
    public function destroyCategory(PriorityFaunaCategory $category)
    {
        try {
            if ($category->priorityFaunas()->count() > 0) {
                return back()->with('error', 'Tidak dapat menghapus kategori yang masih memiliki fauna!');
            }

            $category->delete();
            return back()->with('success', 'Kategori berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('[AdminPriorityFauna] Failed to delete category', [
                'category_id' => $category->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal menghapus kategori!');
        }
    }

    /**
     * Display fauna management
     */
    public function fauna(Request $request)
    {
        $query = PriorityFauna::with(['category']);

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by monitoring status
        if ($request->filled('is_monitored')) {
            $query->where('is_monitored', $request->boolean('is_monitored'));
        }

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('taxa_name', 'like', "%{$search}%")
                  ->orWhere('scientific_name', 'like', "%{$search}%")
                  ->orWhere('common_name', 'like', "%{$search}%");
            });
        }

        $fauna = $query->orderBy('created_at', 'desc')->paginate(20);
        $categories = PriorityFaunaCategory::active()->get();

        return view('admin.priority-fauna.fauna', compact('fauna', 'categories'));
    }

    /**
     * Show form to add new fauna
     */
    public function createFauna()
    {
        $categories = PriorityFaunaCategory::active()->get();
        return view('admin.priority-fauna.create-fauna', compact('categories'));
    }

    /**
     * Store new fauna
     */
    public function storeFauna(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'taxa_id' => 'required|integer',
            'taxa_name' => 'required|string|max:255',
            'scientific_name' => 'nullable|string|max:255',
            'common_name' => 'nullable|string|max:255',
            'iucn_status' => 'nullable|string|max:50',
            'protection_status' => 'nullable|string|max:100',
            'taxa_rank' => 'nullable|string|max:50',
            'taxa_kingdom' => 'nullable|string|max:100',
            'taxa_phylum' => 'nullable|string|max:100',
            'taxa_class' => 'nullable|string|max:100',
            'taxa_order' => 'nullable|string|max:100',
            'taxa_family' => 'nullable|string|max:100',
            'taxa_genus' => 'nullable|string|max:100',
            'taxa_species' => 'nullable|string|max:100',
            'taxa_data_json' => 'nullable|string',
            'category_id' => 'required|exists:priority_fauna_categories,id',
            'notes' => 'nullable|string|max:1000',
            'is_monitored' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Check if taxa already exists
            $existing = PriorityFauna::where('taxa_id', $request->taxa_id)->first();
            if ($existing) {
                return back()->with('error', 'Taksa ini sudah ada dalam daftar prioritas!')->withInput();
            }

            // Parse taxa data from JSON if available
            $taxaData = null;
            if ($request->taxa_data_json) {
                try {
                    $taxaData = json_decode($request->taxa_data_json, true);
                } catch (\Exception $e) {
                    Log::warning('[AdminPriorityFauna] Failed to parse taxa_data_json', [
                        'error' => $e->getMessage(),
                        'json' => $request->taxa_data_json
                    ]);
                }
            }

            Log::info('[AdminPriorityFauna] Creating fauna with data', [
                'taxa_id' => $request->taxa_id,
                'protection_status' => $request->protection_status,
                'iucn_status' => $request->iucn_status,
                'has_taxa_data' => !is_null($taxaData)
            ]);

            $priorityFauna = PriorityFauna::create([
                'taxa_id' => $request->taxa_id,
                'taxa_name' => $request->taxa_name,
                'scientific_name' => $request->scientific_name,
                'common_name' => $request->common_name,
                'taxa_data' => $taxaData,
                'iucn_status' => $request->iucn_status,
                'protection_status' => $request->protection_status,
                'category_id' => $request->category_id,
                'notes' => $request->notes,
                'is_monitored' => $request->boolean('is_monitored', true),
                'last_api_sync' => now(), // Set sync time since we have fresh data from frontend
            ]);

            Log::info('[AdminPriorityFauna] Fauna added successfully', [
                'fauna_id' => $priorityFauna->id,
                'taxa_id' => $request->taxa_id,
                'taxa_name' => $priorityFauna->taxa_name,
                'protection_status' => $priorityFauna->protection_status,
                'iucn_status' => $priorityFauna->iucn_status,
                'source' => 'frontend_form'
            ]);

            return redirect()->route('admin.priority-fauna.fauna')
                ->with('success', 'Fauna prioritas berhasil ditambahkan!');

        } catch (\Exception $e) {
            Log::error('[AdminPriorityFauna] Failed to create fauna', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->with('error', 'Gagal menambahkan fauna prioritas!')->withInput();
        }
    }

    /**
     * Show fauna details
     */
    public function showFauna(PriorityFauna $fauna)
    {
        $fauna->load('category', 'checklist');
        return view('admin.priority-fauna.show-fauna', compact('fauna'));
    }

    /**
     * Update fauna
     */
    public function updateFauna(Request $request, PriorityFauna $fauna)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:priority_fauna_categories,id',
            'notes' => 'nullable|string|max:1000',
            'is_monitored' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $fauna->update($request->only([
                'category_id', 'notes', 'is_monitored'
            ]));

            return back()->with('success', 'Fauna prioritas berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('[AdminPriorityFauna] Failed to update fauna', [
                'fauna_id' => $fauna->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal memperbarui fauna prioritas!');
        }
    }

    /**
     * Delete fauna
     */
    public function destroyFauna(PriorityFauna $fauna)
    {
        try {
            $fauna->delete();
            return back()->with('success', 'Fauna prioritas berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('[AdminPriorityFauna] Failed to delete fauna', [
                'fauna_id' => $fauna->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal menghapus fauna prioritas!');
        }
    }

    /**
     * Sync fauna data with API
     */
    public function syncFauna(PriorityFauna $fauna)
    {
        try {
            $success = $this->amaturalistService->syncPriorityFaunaData($fauna->id);
            
            if ($success) {
                return back()->with('success', 'Data fauna berhasil disinkronisasi!');
            } else {
                return back()->with('error', 'Gagal sinkronisasi data fauna!');
            }
        } catch (\Exception $e) {
            Log::error('[AdminPriorityFauna] Failed to sync fauna', [
                'fauna_id' => $fauna->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal sinkronisasi data fauna!');
        }
    }

    /**
     * Bulk sync all fauna that needs update
     */
    public function bulkSync()
    {
        try {
            $faunaToSync = PriorityFauna::monitored()
                ->get()
                ->filter->needsApiSync();

            $syncCount = 0;
            foreach ($faunaToSync as $fauna) {
                if ($this->amaturalistService->syncPriorityFaunaData($fauna->id)) {
                    $syncCount++;
                }
            }

            return back()->with('success', "Berhasil sinkronisasi {$syncCount} dari {$faunaToSync->count()} fauna!");

        } catch (\Exception $e) {
            Log::error('[AdminPriorityFauna] Failed bulk sync', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal melakukan sinkronisasi massal!');
        }
    }

    /**
     * API endpoint for taxa suggestions
     */
    public function taxaSuggestions(Request $request)
    {
        $query = $request->get('q', '');
        $limit = min($request->get('limit', 10), 50);

        Log::info('[AdminPriorityFauna] Taxa suggestions request', [
            'query' => $query,
            'limit' => $limit,
            'ip' => $request->ip()
        ]);

        try {
            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Query too short'
                ]);
            }

            $result = $this->amaturalistService->getTaxaSuggestions($query, $limit);
            
            Log::info('[AdminPriorityFauna] Taxa suggestions response', [
                'query' => $query,
                'success' => $result['success'] ?? false,
                'count' => isset($result['data']) ? count($result['data']) : 0,
                'source' => $result['source'] ?? 'unknown'
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('[AdminPriorityFauna] Taxa suggestions failed', [
                'query' => $query,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Gagal mengambil saran taksa: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Test endpoint untuk debugging
     */
    public function testEndpoint(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Endpoint berfungsi!',
            'timestamp' => now(),
            'request_data' => $request->all()
        ]);
    }

    /**
     * Review priority fauna observation
     */
    public function reviewObservation(PriorityFaunaObservation $observation)
    {
        try {
            $observation->update([
                'status' => 'reviewed',
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Observasi berhasil direview'
            ]);

        } catch (\Exception $e) {
            Log::error('Error reviewing observation: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status observasi'
            ], 500);
        }
    }
}
