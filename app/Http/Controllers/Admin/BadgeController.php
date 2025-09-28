<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FobiApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BadgeController extends Controller
{
    protected $fobiApi;

    public function __construct(FobiApiService $fobiApi)
    {
        $this->fobiApi = $fobiApi;
    }

    /**
     * Display a listing of badges for Akar application
     */
    public function index(Request $request)
    {
        try {
            // Try API first, fallback to direct DB connection
            if ($this->fobiApi->healthCheck()) {
                return $this->indexViaApi($request);
            } else {
                Log::warning('FOBI API not available, using direct DB connection');
                return $this->indexViaDatabase($request);
            }
        } catch (\Exception $e) {
            Log::error('Badge index error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Terjadi kesalahan saat memuat data badge.');
        }
    }

    /**
     * Index method using API (Recommended)
     */
    private function indexViaApi(Request $request)
    {
        try {
            $filters = [];
            
            // Build filters from request
            if ($request->filled('search')) {
                $filters['search'] = $request->search;
            }
            if ($request->filled('type')) {
                $filters['type'] = $request->type;
            }
            if ($request->filled('has_total')) {
                $filters['has_total'] = $request->has_total;
            }
            
            // Add pagination
            $filters['page'] = $request->get('page', 1);
            $filters['per_page'] = 15;

            $response = $this->fobiApi->getBadges($filters);
            
            if (!$response) {
                return back()->with('error', 'Gagal mengambil data badge dari API.');
            }

            // Get badge types for filter
            $badgeTypesResponse = $this->fobiApi->getBadgeTypes();
            $badgeTypes = $badgeTypesResponse ? $badgeTypesResponse['data'] ?? [] : [];

            return view('admin.badges.index', [
                'badges' => $response['data'] ?? [],
                'pagination' => $response['pagination'] ?? [],
                'badgeTypes' => $badgeTypes,
                'currentFilters' => $filters
            ]);
        } catch (\Exception $e) {
            Log::error('API Badge index error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Terjadi kesalahan saat mengambil data dari API.');
        }
    }

    /**
     * Index method using direct database connection (Fallback)
     */
    private function indexViaDatabase(Request $request)
    {
        try {
            // Connect to main database to get badges
            $mainDB = DB::connection('second');
            
            $query = $mainDB->table('badges')
                ->leftJoin('badge_types', 'badges.type', '=', 'badge_types.id')
                ->select([
                    'badges.*',
                    'badge_types.name as type_name',
                    'badge_types.requires_total'
                ])
                ->where('badges.akar', true) // Only badges for Akar
                ->whereNull('badges.deleted_at');

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('badges.title', 'like', "%{$search}%")
                      ->orWhere('badge_types.name', 'like', "%{$search}%");
                });
            }

            // Filter by type
            if ($request->filled('type')) {
                $query->where('badges.type', $request->type);
            }

            // Filter by total requirement
            if ($request->filled('has_total')) {
                if ($request->has_total == '1') {
                    $query->whereNotNull('badges.total');
                } else {
                    $query->whereNull('badges.total');
                }
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            $allowedSorts = ['id', 'title', 'type', 'total', 'created_at'];
            if (in_array($sortBy, $allowedSorts)) {
                if ($sortBy === 'type') {
                    $query->orderBy('badge_types.name', $sortDirection);
                } else {
                    $query->orderBy("badges.{$sortBy}", $sortDirection);
                }
            }

            $badges = $query->paginate(15)->withQueryString();

            // Get badge types for filter
            $badgeTypes = $mainDB->table('badge_types')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return view('admin.badges.index', compact('badges', 'badgeTypes'));

        } catch (\Exception $e) {
            Log::error('Failed to load badges for Akar admin', [
                'error' => $e->getMessage(),
                'admin_user' => auth()->user()->id ?? 'system'
            ]);

            return redirect()->back()
                ->with('error', 'Gagal memuat data badge: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new badge
     */
    public function create()
    {
        try {
            $mainDB = DB::connection('second');
            
            // Get active badge types
            $badgeTypes = $mainDB->table('badge_types')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return view('admin.badges.create', compact('badgeTypes'));

        } catch (\Exception $e) {
            Log::error('Failed to load badge create form for Akar admin', [
                'error' => $e->getMessage(),
                'admin_user' => auth()->user()->id ?? 'system'
            ]);

            return redirect()->route('admin.badges.index')
                ->with('error', 'Gagal memuat form create badge: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created badge
     */
    public function store(Request $request)
    {
        try {
            $mainDB = DB::connection('second');
            
            // Get available badge type IDs dynamically
            $availableBadgeTypeIds = $mainDB->table('badge_types')
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
            
            $badgeTypeIdsRule = 'required|integer|in:' . implode(',', $availableBadgeTypeIds);
            
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'type' => $badgeTypeIdsRule,
                'total' => 'nullable|integer|min:1|max:10000',
                'icon_active' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'icon_unactive' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'images_congrats' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'text_congrats_1' => 'nullable|string|max:500',
                'text_congrats_2' => 'nullable|string|max:500',
                'text_congrats_3' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Check if badge type requires total
            $badgeType = $mainDB->table('badge_types')
                ->where('id', $request->type)
                ->first();

            if ($badgeType && $badgeType->requires_total && !$request->total) {
                return redirect()->back()
                    ->withErrors(['total' => 'Tipe badge ini memerlukan target total.'])
                    ->withInput();
            }

            $mainDB->beginTransaction();

            // Check for duplicate title
            $existingBadge = $mainDB->table('badges')
                ->where('title', $request->title)
                ->whereNull('deleted_at')
                ->first();

            if ($existingBadge) {
                return redirect()->back()
                    ->withErrors(['title' => 'Judul badge sudah digunakan.'])
                    ->withInput();
            }

            // Prepare badge data
            $badgeData = [
                'title' => $request->title,
                'type' => $request->type,
                'total' => $badgeType && $badgeType->requires_total ? $request->total : null,
                'text_congrats_1' => $request->text_congrats_1,
                'text_congrats_2' => $request->text_congrats_2,
                'text_congrats_3' => $request->text_congrats_3,
                'fobi' => false,
                'burungnesia' => false,
                'kupunesia' => false,
                'akar' => true, // Always true for Akar admin
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Handle file uploads
            if ($request->hasFile('icon_active')) {
                $file = $request->file('icon_active');
                $filename = 'badge_icon_active_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('badges', $filename, 'public');
                $badgeData['icon_active'] = 'storage/' . $path;
            }

            if ($request->hasFile('icon_unactive')) {
                $file = $request->file('icon_unactive');
                $filename = 'badge_icon_unactive_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('badges', $filename, 'public');
                $badgeData['icon_unactive'] = 'storage/' . $path;
            }

            if ($request->hasFile('images_congrats')) {
                $file = $request->file('images_congrats');
                $filename = 'badge_congrats_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('badges', $filename, 'public');
                $badgeData['images_congrats'] = 'storage/' . $path;
            }

            // Insert badge
            $badgeId = $mainDB->table('badges')->insertGetId($badgeData);

            // Log activity
            Log::info('Badge created by Akar admin', [
                'badge_id' => $badgeId,
                'title' => $badgeData['title'],
                'admin_user' => auth()->user()->id ?? 'system'
            ]);

            $mainDB->commit();

            return redirect()->route('admin.badges.index')
                ->with('success', 'Badge berhasil dibuat!');

        } catch (\Exception $e) {
            if (isset($mainDB)) {
                $mainDB->rollback();
            }
            
            Log::error('Failed to create badge in Akar admin', [
                'error' => $e->getMessage(),
                'admin_user' => auth()->user()->id ?? 'system'
            ]);

            return redirect()->back()
                ->with('error', 'Gagal membuat badge: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified badge
     */
    public function show($id)
    {
        try {
            $mainDB = DB::connection('second');
            
            $badge = $mainDB->table('badges')
                ->leftJoin('badge_types', 'badges.type', '=', 'badge_types.id')
                ->select([
                    'badges.*',
                    'badge_types.name as type_name',
                    'badge_types.description as type_description',
                    'badge_types.requires_total'
                ])
                ->where('badges.id', $id)
                ->where('badges.akar', true)
                ->whereNull('badges.deleted_at')
                ->first();

            if (!$badge) {
                return redirect()->route('admin.badges.index')
                    ->with('error', 'Badge tidak ditemukan atau tidak tersedia untuk aplikasi Akar.');
            }

            return view('admin.badges.show', compact('badge'));

        } catch (\Exception $e) {
            Log::error('Failed to show badge in Akar admin', [
                'badge_id' => $id,
                'error' => $e->getMessage(),
                'admin_user' => auth()->user()->id ?? 'system'
            ]);

            return redirect()->route('admin.badges.index')
                ->with('error', 'Gagal memuat detail badge: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified badge
     */
    public function edit($id)
    {
        try {
            $mainDB = DB::connection('second');
            
            $badge = $mainDB->table('badges')
                ->where('id', $id)
                ->where('akar', true)
                ->whereNull('deleted_at')
                ->first();

            if (!$badge) {
                return redirect()->route('admin.badges.index')
                    ->with('error', 'Badge tidak ditemukan atau tidak tersedia untuk aplikasi Akar.');
            }

            // Get active badge types
            $badgeTypes = $mainDB->table('badge_types')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return view('admin.badges.edit', compact('badge', 'badgeTypes'));

        } catch (\Exception $e) {
            Log::error('Failed to load badge edit form in Akar admin', [
                'badge_id' => $id,
                'error' => $e->getMessage(),
                'admin_user' => auth()->user()->id ?? 'system'
            ]);

            return redirect()->route('admin.badges.index')
                ->with('error', 'Gagal memuat form edit badge: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified badge
     */
    public function update(Request $request, $id)
    {
        try {
            $mainDB = DB::connection('second');
            
            $badge = $mainDB->table('badges')
                ->where('id', $id)
                ->where('akar', true)
                ->whereNull('deleted_at')
                ->first();

            if (!$badge) {
                return redirect()->route('admin.badges.index')
                    ->with('error', 'Badge tidak ditemukan atau tidak tersedia untuk aplikasi Akar.');
            }

            // Get available badge type IDs dynamically
            $availableBadgeTypeIds = $mainDB->table('badge_types')
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
            
            $badgeTypeIdsRule = 'required|integer|in:' . implode(',', $availableBadgeTypeIds);
            
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'type' => $badgeTypeIdsRule,
                'total' => 'nullable|integer|min:1|max:10000',
                'icon_active' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'icon_unactive' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'images_congrats' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'text_congrats_1' => 'nullable|string|max:500',
                'text_congrats_2' => 'nullable|string|max:500',
                'text_congrats_3' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Check if badge type requires total
            $badgeType = $mainDB->table('badge_types')
                ->where('id', $request->type)
                ->first();

            if ($badgeType && $badgeType->requires_total && !$request->total) {
                return redirect()->back()
                    ->withErrors(['total' => 'Tipe badge ini memerlukan target total.'])
                    ->withInput();
            }

            $mainDB->beginTransaction();

            // Check for duplicate title (excluding current badge)
            $existingBadge = $mainDB->table('badges')
                ->where('title', $request->title)
                ->where('id', '!=', $id)
                ->whereNull('deleted_at')
                ->first();

            if ($existingBadge) {
                return redirect()->back()
                    ->withErrors(['title' => 'Judul badge sudah digunakan.'])
                    ->withInput();
            }

            // Prepare update data
            $updateData = [
                'title' => $request->title,
                'type' => $request->type,
                'total' => $badgeType && $badgeType->requires_total ? $request->total : null,
                'text_congrats_1' => $request->text_congrats_1,
                'text_congrats_2' => $request->text_congrats_2,
                'text_congrats_3' => $request->text_congrats_3,
                'updated_at' => now(),
            ];

            // Handle file uploads
            if ($request->hasFile('icon_active')) {
                // Delete old file if exists
                if ($badge->icon_active && Storage::disk('public')->exists(str_replace('storage/', '', $badge->icon_active))) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $badge->icon_active));
                }
                
                $file = $request->file('icon_active');
                $filename = 'badge_icon_active_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('badges', $filename, 'public');
                $updateData['icon_active'] = 'storage/' . $path;
            }

            if ($request->hasFile('icon_unactive')) {
                // Delete old file if exists
                if ($badge->icon_unactive && Storage::disk('public')->exists(str_replace('storage/', '', $badge->icon_unactive))) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $badge->icon_unactive));
                }
                
                $file = $request->file('icon_unactive');
                $filename = 'badge_icon_unactive_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('badges', $filename, 'public');
                $updateData['icon_unactive'] = 'storage/' . $path;
            }

            if ($request->hasFile('images_congrats')) {
                // Delete old file if exists
                if ($badge->images_congrats && Storage::disk('public')->exists(str_replace('storage/', '', $badge->images_congrats))) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $badge->images_congrats));
                }
                
                $file = $request->file('images_congrats');
                $filename = 'badge_congrats_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('badges', $filename, 'public');
                $updateData['images_congrats'] = 'storage/' . $path;
            }

            // Update badge
            $mainDB->table('badges')
                ->where('id', $id)
                ->update($updateData);

            // Log activity
            Log::info('Badge updated by Akar admin', [
                'badge_id' => $id,
                'title' => $updateData['title'],
                'admin_user' => auth()->user()->id ?? 'system'
            ]);

            $mainDB->commit();

            return redirect()->route('admin.badges.index')
                ->with('success', 'Badge berhasil diupdate!');

        } catch (\Exception $e) {
            if (isset($mainDB)) {
                $mainDB->rollback();
            }
            
            Log::error('Failed to update badge in Akar admin', [
                'badge_id' => $id,
                'error' => $e->getMessage(),
                'admin_user' => auth()->user()->id ?? 'system'
            ]);

            return redirect()->back()
                ->with('error', 'Gagal mengupdate badge: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified badge from storage (soft delete)
     */
    public function destroy($id)
    {
        try {
            $mainDB = DB::connection('second');
            
            $badge = $mainDB->table('badges')
                ->where('id', $id)
                ->where('akar', true)
                ->whereNull('deleted_at')
                ->first();

            if (!$badge) {
                return redirect()->route('admin.badges.index')
                    ->with('error', 'Badge tidak ditemukan atau tidak tersedia untuk aplikasi Akar.');
            }

            // Soft delete
            $mainDB->table('badges')
                ->where('id', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            // Log activity
            Log::info('Badge deleted by Akar admin', [
                'badge_id' => $id,
                'title' => $badge->title,
                'admin_user' => auth()->user()->id ?? 'system'
            ]);

            return redirect()->route('admin.badges.index')
                ->with('success', 'Badge berhasil dihapus!');

        } catch (\Exception $e) {
            Log::error('Failed to delete badge in Akar admin', [
                'badge_id' => $id,
                'error' => $e->getMessage(),
                'admin_user' => auth()->user()->id ?? 'system'
            ]);

            return redirect()->route('admin.badges.index')
                ->with('error', 'Gagal menghapus badge: ' . $e->getMessage());
        }
    }
}
