<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Checklist;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\ChecklistFauna;
use App\Models\ChecklistImage;
use App\Services\SpeciesDataService;
use App\Services\BadgeMemberService;
use App\Models\PriorityFauna;
use App\Models\PriorityFaunaObservation;

class ChecklistController extends Controller
{
    protected BadgeMemberService $badgeMemberService;

    public function __construct(BadgeMemberService $badgeMemberService)
    {
        $this->badgeMemberService = $badgeMemberService;
    }

    /**
     * Display a listing of the resource for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $user->checklists()->with('faunas');

        // Handle search functionality
        // Handle date range filtering
        if ($request->has('start_date') && $request->has('end_date') && !empty($request->input('start_date')) && !empty($request->input('end_date'))) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        }

        // Handle search functionality
        if ($request->has('q') && !empty($request->input('q'))) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('nama_lokasi', 'like', "%{$search}%")
                  ->orWhereHas('faunas', function ($faunasQuery) use ($search) {
                      $faunasQuery->where('nama_spesies', 'like', "%{$search}%");
                  })
                  ->orWhereHas('faunas', function ($faunasQuery) use ($search) {
                      $faunasQuery->where('nama_latin', 'like', "%{$search}%");
                  })
                  ->orWhereHas('faunas', function ($faunasQuery) use ($search) {
                      $faunasQuery->where('type', 'like', "%{$search}%");
                  });
            });
        }

        // Handle sorting
        $sortBy = $request->input('sort_by', 'created_at_desc'); // Default sort

        switch ($sortBy) {
            case 'created_at_asc':
                $query->orderBy('tanggal', 'asc');
                break;
            case 'location_asc':
                $query->orderBy('nama_lokasi', 'asc');
                break;
            case 'location_desc':
                $query->orderBy('nama_lokasi', 'desc');
                break;
            case 'created_at_desc':
            default:
                $query->orderBy('tanggal', 'desc');
                break;
        }

        $checklists = $query->get();

        $data = $checklists->map(function ($checklist) {
            // Calculate species and individual counts
            $speciesCount = $checklist->faunas->pluck('nama_spesies')->unique()->count();
            $individualCount = $checklist->faunas->sum('jumlah');

            // Map checklist type to icon properties for the frontend
            $iconMapping = [
                'Pemeliharaan' => ['icon' => 'leaf-outline', 'iconBgColor' => '#48C9B0'],
                'Perburuan' => ['icon' => 'locate-outline', 'iconBgColor' => '#E74C3C'],
                'Penangkaran' => ['icon' => 'leaf-outline', 'iconBgColor' => '#48C9B0'],
            ];

            $iconInfo = $iconMapping[$checklist->type] ?? ['icon' => 'help-circle-outline', 'iconBgColor' => '#888888'];

            return [
                'id' => $checklist->id,
                'type' => $checklist->type,
                'status' => $checklist->status, // Add status to the response
                'location' => $checklist->nama_lokasi,
                'latitude' => $checklist->latitude,
                'longitude' => $checklist->longitude,
                'speciesCount' => $speciesCount,
                'individualCount' => $individualCount,
                'date' => [
                    'day' => Carbon::parse($checklist->tanggal)->format('d'),
                    'month' => strtoupper(Carbon::parse($checklist->tanggal)->format('M')),
                ],
                'icon' => $iconInfo['icon'],
                'iconColor' => '#fff',
                'iconBgColor' => $iconInfo['iconBgColor'],
            ];
        });

        return response()->json($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Checklist  $checklist
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Checklist $checklist, SpeciesDataService $speciesDataService)
    {
        // Ensure the user can only see their own checklists
        if ($checklist->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Eager load relationships for efficiency
        $checklist->load('user', 'faunas.fauna', 'images.fauna');

        // Format the response data
        $data = [
            'id' => $checklist->id,
            'status' => $checklist->status,
            'type' => $checklist->type,
            'location' => $checklist->nama_lokasi,
            'latitude' => $checklist->latitude,
            'longitude' => $checklist->longitude,
            'date' => Carbon::parse($checklist->tanggal)->isoFormat('D MMMM YYYY'),
            'observer' => $checklist->user->name,
            'notes' => $checklist->catatan,
            'speciesCount' => $checklist->faunas->pluck('nama_spesies')->unique()->count(),
            'individualCount' => $checklist->faunas->sum('jumlah'),
            'photoCount' => $checklist->images->count(),
            'faunas' => $checklist->faunas->map(function ($fauna) use ($speciesDataService) {
                $scientificName = $fauna->nama_latin ?? null;
                return [
                    'id' => $fauna->id,
                    'speciesName' => $fauna->nama_spesies ?? 'N/A',
                    'scientificName' => $scientificName,
                    'fauna_id' => $fauna->fauna_id,
                    'ringed' => $fauna->cincin,
                    // Perubahan disini - pastikan nilai enum dikembalikan apa adanya
                    'status_buruan' => $fauna->status_buruan, // Kembalikan nilai enum asli
                    'tagging_status' => $fauna->tagging_status,
                    'imageUrl' => $fauna->fauna->image_url ?? null,
                    'iucn_status' => $speciesDataService->getIucnStatus($scientificName),
                    'cites_appendix' => $speciesDataService->getCitesAppendix($scientificName),
                    'gender' => $fauna->gender,
                    'total' => $fauna->jumlah,
                    'notes' => $fauna->catatan,
                ];
            }),
            'images' => $checklist->images->map(function ($image) {
                return [
                    'path' => Storage::url($image->image_path),
                    'fauna' => $image->fauna ? [
                        'id' => $image->fauna->id,
                        'image_url' => $image->fauna->image_url,
                    ] : null,
                ];
            })
        ];

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validationRules = [
            'data' => 'required|json',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,bmp,gif,tiff|max:10240', // Increased to 10MB and added more formats
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = json_decode($request->input('data'), true);

        $dataValidator = Validator::make($data, [
            'type' => 'required|string|in:pemeliharaan,perdagangan,lomba,perburuan,lainnya',
            'status' => 'required|string|in:published,draft',
            'is_completed' => 'required|boolean',
            'admin_data' => 'required|array',
            'admin_data.date' => 'required|date',
            'admin_data.location' => 'nullable|string|max:255',
            'admin_data.latitude' => 'nullable|numeric|between:-90,90',
            'admin_data.longitude' => 'nullable|numeric|between:-180,180',
            'admin_data.notes' => 'nullable|string',
            'species_list' => 'required|array|min:1',
            'species_list.*.speciesName' => 'required|string|max:255',
            'species_list.*.scientificName' => 'required|string|max:255',
            'species_list.*.fauna_id' => 'nullable|integer',
            'species_list.*.quantity' => 'required|integer|min:1',
            'species_list.*.photoCount' => 'required|integer|min:0',
            'species_list.*.tagging_status' => 'nullable|string|max:255',
            'species_list.*.hunting_status' => 'nullable|string|max:255',
            'species_list.*.gender' => 'nullable|string|max:255',
        ]);

        if ($dataValidator->fails()) {
            return response()->json($dataValidator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            \Log::info('[ChecklistController] Starting checklist creation', [
                'user_id' => Auth::id(),
                'type' => $data['type'],
                'species_count' => count($data['species_list']),
                'has_photos' => $request->hasFile('photos'),
                'photo_files_count' => $request->hasFile('photos') ? count($request->file('photos')) : 0
            ]);

            // Validasi storage directory
            $storagePath = storage_path('app/public/checklist_images');
            if (!is_dir($storagePath)) {
                \Log::info('[ChecklistController] Creating storage directory', ['path' => $storagePath]);
                if (!mkdir($storagePath, 0755, true)) {
                    \Log::error('[ChecklistController] Failed to create storage directory', ['path' => $storagePath]);
                    throw new \Exception('Unable to create storage directory');
                }
            }

            // Cek permission storage directory
            if (!is_writable($storagePath)) {
                \Log::error('[ChecklistController] Storage directory not writable', ['path' => $storagePath]);
                throw new \Exception('Storage directory is not writable');
            }

            \Log::info('[ChecklistController] Storage validation passed', ['path' => $storagePath]);

            $status = isset($data['status']) && in_array($data['status'], ['published', 'draft']) ? $data['status'] : 'draft';

            $checklist = Checklist::create([
                'user_id' => Auth::id(),
                'type' => $data['type'],
                'is_completed' => $data['is_completed'],
                'status' => $status,
                'tanggal' => Carbon::parse($data['admin_data']['date']),
                'nama_lokasi' => $data['admin_data']['location'] ?? ($data['admin_data']['latitude'] . ', ' . $data['admin_data']['longitude']),
                'latitude' => $data['admin_data']['latitude'] ?? null,
                'longitude' => $data['admin_data']['longitude'] ?? null,
                'catatan' => $data['admin_data']['notes'] ?? null,
                'nama_event' => $data['admin_data']['eventName'] ?? null,
                'teknik_berburu' => $data['admin_data']['huntingTool'] ?? null,
            ]);

            \Log::info('[ChecklistController] Checklist created successfully', ['checklist_id' => $checklist->id]);

            $photos = $request->file('photos') ?? [];
            $photoIndex = 0;
            
            \Log::info('[ChecklistController] Photo processing info', [
                'total_photos_received' => count($photos),
                'photo_index_start' => $photoIndex
            ]);

            foreach ($data['species_list'] as $speciesIndex => $speciesData) {
                \Log::info('[ChecklistController] Processing species', [
                    'species_index' => $speciesIndex,
                    'species_name' => $speciesData['speciesName'],
                    'photo_count' => $speciesData['photoCount'],
                    'current_photo_index' => $photoIndex
                ]);

                $faunaData = [
                    'nama_spesies' => $speciesData['speciesName'],
                    'nama_latin' => $speciesData['scientificName'],
                    'fauna_id' => $speciesData['fauna_id'] ?? null,
                    'jumlah' => $speciesData['quantity'],
                    'gender' => $speciesData['gender'] ?? null,
                    'catatan' => $speciesData['notes'] ?? null,
                    'status_buruan' => $speciesData['hunting_status'] ?? null,
                    'tagging_status' => $speciesData['tagging_status'] ?? null,
                ];

                $fauna = $checklist->faunas()->create($faunaData);
                \Log::info('[ChecklistController] Fauna created', ['fauna_id' => $fauna->id]);

                $photoCountForSpecies = $speciesData['photoCount'];
                $speciesPhotos = array_slice($photos, $photoIndex, $photoCountForSpecies);

                \Log::info('[ChecklistController] Photo slice info', [
                    'photo_count_for_species' => $photoCountForSpecies,
                    'photos_sliced' => count($speciesPhotos),
                    'photo_index_start' => $photoIndex,
                    'photo_index_end' => $photoIndex + $photoCountForSpecies
                ]);

                foreach ($speciesPhotos as $photoIndex2 => $photo) {
                    try {
                        \Log::info('[ChecklistController] Processing photo', [
                            'photo_index' => $photoIndex2,
                            'photo_size' => $photo->getSize(),
                            'photo_mime' => $photo->getMimeType(),
                            'photo_original_name' => $photo->getClientOriginalName()
                        ]);

                        $filename = uniqid() . '.webp';
                        
                        // Validasi file foto
                        if (!$photo->isValid()) {
                            \Log::error('[ChecklistController] Invalid photo file', ['photo_index' => $photoIndex2]);
                            throw new \Exception('Invalid photo file at index ' . $photoIndex2);
                        }

                        // Cek ukuran file (maksimal 10MB)
                        if ($photo->getSize() > 10 * 1024 * 1024) {
                            \Log::error('[ChecklistController] Photo too large', [
                                'photo_index' => $photoIndex2,
                                'size' => $photo->getSize()
                            ]);
                            throw new \Exception('Photo file too large at index ' . $photoIndex2);
                        }

                        // Coba baca dan proses gambar - convert semua format ke WebP
                        \Log::info('[ChecklistController] Starting image processing', [
                            'original_mime' => $photo->getMimeType(),
                            'original_size' => $photo->getSize()
                        ]);
                        
                        $image = Image::read($photo)->resize(1280, null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                        \Log::info('[ChecklistController] Image resized successfully');
                        
                        // Convert ke WebP dengan kualitas 80 untuk balance antara ukuran dan kualitas
                        $compressedImage = $image->encode(new WebpEncoder(quality: 80));
                        \Log::info('[ChecklistController] Image converted to WebP successfully', [
                            'compressed_size' => strlen((string) $compressedImage),
                            'compression_ratio' => round((1 - strlen((string) $compressedImage) / $photo->getSize()) * 100, 2) . '%'
                        ]);

                        $path = 'checklist_images/' . $filename;
                        $stored = Storage::disk('public')->put($path, (string) $compressedImage);
                        
                        if (!$stored) {
                            \Log::error('[ChecklistController] Failed to store photo', ['path' => $path]);
                            throw new \Exception('Failed to store photo file');
                        }

                        $imageRecord = $checklist->images()->create([
                            'image_path' => $path,
                            'fauna_id' => $speciesData['fauna_id'] ?? null,
                        ]);

                        \Log::info('[ChecklistController] Photo processed successfully', [
                            'image_id' => $imageRecord->id,
                            'path' => $path,
                            'fauna_id' => $speciesData['fauna_id'] ?? null
                        ]);

                    } catch (\Exception $photoError) {
                        \Log::error('[ChecklistController] Photo processing error', [
                            'photo_index' => $photoIndex2,
                            'error' => $photoError->getMessage(),
                            'trace' => $photoError->getTraceAsString()
                        ]);
                        throw $photoError; // Re-throw to trigger rollback
                    }
                }

                $photoIndex += $photoCountForSpecies;
                \Log::info('[ChecklistController] Species processing completed', [
                    'species_index' => $speciesIndex,
                    'new_photo_index' => $photoIndex
                ]);
            }

            DB::commit();

            // Check for priority fauna observations after successful checklist creation
            $this->checkPriorityFaunaObservations($checklist, $data);

            // Badge tracking - check for new badges after successful checklist creation
            $newBadges = [];
            try {
                if ($status === 'published') { // Remove is_completed requirement
                    // Get user's current checklist count (remove is_completed requirement)
                    $currentChecklistCount = Auth::user()->checklists()
                        ->where('status', 'published')
                        ->count();
                    
                    $previousCount = $currentChecklistCount - 1;
                    
                    \Log::info('Badge tracking - Checklist published', [
                        'user_id' => Auth::id(),
                        'checklist_id' => $checklist->id,
                        'current_count' => $currentChecklistCount,
                        'previous_count' => $previousCount,
                        'species_count' => isset($data['species_list']) ? count($data['species_list']) : 0
                    ]);
                    
                    // Check for new badges
                    $rawNewBadges = $this->badgeMemberService->checkNewBadgesForMember(
                        Auth::id(),
                        $currentChecklistCount,
                        $previousCount
                    );
                    
                    // Validate new badges structure
                    if (is_array($rawNewBadges)) {
                        foreach ($rawNewBadges as $badge) {
                            if (is_array($badge) && 
                                isset($badge['badge_id']) && 
                                isset($badge['badge_data']) && 
                                is_array($badge['badge_data']) &&
                                isset($badge['badge_data']['title'])) {
                                $newBadges[] = $badge;
                            } else {
                                \Log::warning('Invalid new badge structure detected', ['badge' => $badge]);
                            }
                        }
                    }
                    
                    if (!empty($newBadges)) {
                        \Log::info('New badges earned!', [
                            'badges_count' => count($newBadges),
                            'badges' => $newBadges
                        ]);
                    } else {
                        \Log::info('Badge progress updated, no new badges earned');
                    }
                }
            } catch (\Exception $badgeError) {
                \Log::error('Badge tracking error: ' . $badgeError->getMessage(), [
                    'trace' => $badgeError->getTraceAsString()
                ]);
                $newBadges = []; // Ensure it's always an array
                // Don't fail the checklist creation if badge tracking fails
            }

            // Get updated badge progress after checklist creation
            $badgeProgress = [];
            try {
                if ($status === 'published') {
                    // Clean output buffer to prevent malformed JSON
                    if (ob_get_level()) {
                        ob_clean();
                    }
                    
                    $badgeProgress = $this->badgeMemberService->getMemberBadgeProgress(Auth::id());
                    
                    // Validate badge progress structure
                    if (!is_array($badgeProgress)) {
                        \Log::warning('Badge progress is not an array, converting to empty array');
                        $badgeProgress = [];
                    }
                    
                    // Validate each badge item
                    $validatedProgress = [];
                    foreach ($badgeProgress as $badge) {
                        if (is_array($badge) && 
                            isset($badge['badge_id']) && 
                            isset($badge['badge_data']) && 
                            is_array($badge['badge_data']) &&
                            isset($badge['badge_data']['title'])) {
                            $validatedProgress[] = $badge;
                        } else {
                            \Log::warning('Invalid badge structure detected', ['badge' => $badge]);
                        }
                    }
                    $badgeProgress = $validatedProgress;
                    
                    \Log::info('Badge progress retrieved successfully', [
                        'user_id' => Auth::id(),
                        'progress_count' => count($badgeProgress),
                        'validated_count' => count($validatedProgress)
                    ]);
                }
            } catch (\Exception $progressError) {
                \Log::error('Error getting badge progress: ' . $progressError->getMessage(), [
                    'trace' => $progressError->getTraceAsString()
                ]);
                $badgeProgress = []; // Ensure it's always an array
            }

            // Clean output buffer before JSON response
            if (ob_get_level()) {
                ob_clean();
            }

            $responseData = [
                'message' => 'Checklist created successfully',
                'checklist_id' => $checklist->id,
                'new_badges' => $newBadges ?? [], // Ensure array
                'badge_progress' => $badgeProgress ?? [], // Ensure array
                'badge_refresh_needed' => !empty($newBadges) || !empty($badgeProgress)
            ];

            \Log::info('Checklist response prepared', [
                'checklist_id' => $checklist->id,
                'has_new_badges' => !empty($newBadges),
                'has_badge_progress' => !empty($badgeProgress),
                'response_size' => strlen(json_encode($responseData))
            ]);

            return response()->json($responseData, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('[ChecklistController] Checklist creation failed', [
                'user_id' => Auth::id(),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return more specific error message based on error type
            $errorMessage = 'Failed to create checklist';
            if (strpos($e->getMessage(), 'photo') !== false) {
                $errorMessage = 'Failed to process photo: ' . $e->getMessage();
            } elseif (strpos($e->getMessage(), 'storage') !== false || strpos($e->getMessage(), 'disk') !== false) {
                $errorMessage = 'Storage error: Unable to save files';
            } elseif (strpos($e->getMessage(), 'database') !== false || strpos($e->getMessage(), 'SQL') !== false) {
                $errorMessage = 'Database error: Unable to save data';
            }
            
            return response()->json([
                'message' => $errorMessage, 
                'error' => $e->getMessage(),
                'debug_info' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Checklist  $checklist
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Checklist $checklist)
    {
        // Ensure the user can only update their own checklists
        if ($checklist->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validationRules = [
            'data' => 'required|json',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,bmp,gif,tiff|max:10240', // Support more formats, convert all to WebP
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = json_decode($request->input('data'), true);

        $dataValidator = Validator::make($data, [
            'type' => 'required|string|in:pemeliharaan,perdagangan,lomba,perburuan,lainnya',
            'status' => 'required|string|in:published,draft',
            'is_completed' => 'required|boolean',
            'admin_data' => 'required|array',
            'admin_data.date' => 'required|date',
            'admin_data.location' => 'nullable|string|max:255',
            'admin_data.latitude' => 'nullable|numeric|between:-90,90',
            'admin_data.longitude' => 'nullable|numeric|between:-180,180',
            'admin_data.notes' => 'nullable|string',
            'species_list' => 'required|array|min:1',
            'species_list.*.speciesName' => 'required|string|max:255',
            'species_list.*.scientificName' => 'required|string|max:255',
            'species_list.*.fauna_id' => 'nullable|integer',
            'species_list.*.quantity' => 'required|integer|min:1',
            'species_list.*.photoCount' => 'present|integer|min:0',
            'species_list.*.gender' => 'nullable|string|max:255',
            'species_list.*.hunting_status' => 'nullable|string|max:255',
            'species_list.*.tagging_status' => 'nullable|string|max:255',
        ]);

        if ($dataValidator->fails()) {
            return response()->json($dataValidator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            // Update checklist basic info
            $checklist->update([
                'type' => $data['type'],
                'is_completed' => $data['is_completed'],
                'status' => $data['status'],
                'tanggal' => Carbon::parse($data['admin_data']['date']),
                'nama_lokasi' => $data['admin_data']['location'] ?? ($data['admin_data']['latitude'] . ', ' . $data['admin_data']['longitude']),
                'latitude' => $data['admin_data']['latitude'] ?? null,
                'longitude' => $data['admin_data']['longitude'] ?? null,
                'catatan' => $data['admin_data']['notes'] ?? null,
                'nama_event' => $data['admin_data']['eventName'] ?? null,
                'teknik_berburu' => $data['admin_data']['huntingTool'] ?? null,
            ]);

            // Delete existing fauna
            $checklist->faunas()->delete();
            
            // Get existing images but don't delete them yet
            $existingImages = $checklist->images()->get();
            
            // Keep track of which images we should keep
            $imagesToKeep = [];
            
            $photos = $request->file('photos') ?? [];
            $photoIndex = 0;

            foreach ($data['species_list'] as $speciesData) {
                $faunaData = [
                    'nama_spesies' => $speciesData['speciesName'],
                    'nama_latin' => $speciesData['scientificName'],
                    'fauna_id' => $speciesData['fauna_id'] ?? null,
                    'jumlah' => $speciesData['quantity'],
                    'gender' => $speciesData['gender'] ?? null,
                    'catatan' => $speciesData['notes'] ?? null,
                ];

                $faunaData['status_buruan'] = $speciesData['hunting_status'] ?? null;
                $faunaData['tagging_status'] = $speciesData['tagging_status'] ?? null;

                $fauna = $checklist->faunas()->create($faunaData);

                // Hanya proses foto jika ada foto baru
                $photoCountForSpecies = $speciesData['photoCount'] ?? 0;
                if ($photoCountForSpecies > 0) {
                    $speciesPhotos = array_slice($photos, $photoIndex, $photoCountForSpecies);

                    foreach ($speciesPhotos as $photo) {
                        $filename = uniqid() . '.webp';
                        $image = Image::read($photo)->resize(1280, null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                        // Convert ke WebP untuk konsistensi dengan method store
                        $compressedImage = $image->encode(new WebpEncoder(quality: 80));

                        $path = 'checklist_images/' . $filename;
                        Storage::disk('public')->put($path, (string) $compressedImage);

                        $checklist->images()->create([
                            'image_path' => $path,
                            'fauna_id' => $speciesData['fauna_id'] ?? null,
                        ]);
                    }

                    $photoIndex += $photoCountForSpecies;
                }
                
                // Jika fauna_id ada di existingImages, tambahkan ke imagesToKeep
                if ($speciesData['fauna_id']) {
                    foreach ($existingImages as $image) {
                        if ($image->fauna_id == $speciesData['fauna_id']) {
                            $imagesToKeep[] = $image->id;
                        }
                    }
                }
            }

            // Hapus gambar yang tidak digunakan lagi
            foreach ($existingImages as $image) {
                if (!in_array($image->id, $imagesToKeep)) {
                    if (Storage::disk('public')->exists($image->image_path)) {
                        Storage::disk('public')->delete($image->image_path);
                    }
                    $image->delete();
                }
            }

            DB::commit();

            return response()->json(['message' => 'Checklist updated successfully', 'checklist_id' => $checklist->id], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update checklist', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update only the status of a checklist (for publishing drafts).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Checklist  $checklist
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, Checklist $checklist)
    {
        // Ensure the user can only update their own checklists
        if ($checklist->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:published,draft',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $checklist->update([
                'status' => $request->status
            ]);

            return response()->json([
                'message' => 'Checklist status updated successfully',
                'status' => $request->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update checklist status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a single checklist (soft delete).
     *
     * @param  \App\Models\Checklist  $checklist
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Checklist $checklist)
    {
        try {
            // Check if the checklist belongs to the authenticated user
            if ($checklist->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Unauthorized to delete this checklist'
                ], 403);
            }

            // Soft delete the checklist
            $checklist->delete();

            // Log activity
            activity_log('delete', "User deleted checklist: {$checklist->type} at {$checklist->nama_lokasi}");

            return response()->json([
                'message' => 'Checklist berhasil dihapus',
                'data' => [
                    'id' => $checklist->id,
                    'deleted_at' => $checklist->deleted_at
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus checklist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete multiple checklists (soft delete).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array|min:1',
                'ids.*' => 'required|integer|exists:checklists,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ids = $request->input('ids');
            $user = Auth::user();

            // Get checklists that belong to the authenticated user
            $checklists = Checklist::whereIn('id', $ids)
                ->where('user_id', $user->id)
                ->get();

            if ($checklists->count() !== count($ids)) {
                return response()->json([
                    'message' => 'Some checklists not found or unauthorized'
                ], 403);
            }

            // Soft delete all checklists
            $deletedCount = 0;
            $deletedIds = [];

            foreach ($checklists as $checklist) {
                $checklist->delete();
                $deletedCount++;
                $deletedIds[] = $checklist->id;
            }

            // Log activity
            activity_log('bulk_delete', "User deleted {$deletedCount} checklists");

            return response()->json([
                'message' => "{$deletedCount} checklist berhasil dihapus",
                'data' => [
                    'deleted_count' => $deletedCount,
                    'deleted_ids' => $deletedIds
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus checklist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Publish a single draft checklist.
     *
     * @param  \App\Models\Checklist  $checklist
     * @return \Illuminate\Http\JsonResponse
     */
    public function publish(Checklist $checklist)
    {
        try {
            // Check if the checklist belongs to the authenticated user
            if ($checklist->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Unauthorized to publish this checklist'
                ], 403);
            }

            // Check if the checklist is a draft
            if ($checklist->status !== 'draft') {
                return response()->json([
                    'message' => 'Only draft checklists can be published'
                ], 400);
            }

            // Update status to published
            $checklist->update([
                'status' => 'published',
                'published_at' => now()
            ]);

            // Log activity
            activity_log('publish', "User published checklist: {$checklist->type} at {$checklist->nama_lokasi}");

            return response()->json([
                'message' => 'Draft berhasil dipublikasikan',
                'data' => [
                    'id' => $checklist->id,
                    'status' => $checklist->status,
                    'published_at' => $checklist->published_at
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mempublikasikan draft',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Publish multiple draft checklists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkPublish(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array|min:1',
                'ids.*' => 'required|integer|exists:checklists,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ids = $request->input('ids');
            $user = Auth::user();

            // Get draft checklists that belong to the authenticated user
            $checklists = Checklist::whereIn('id', $ids)
                ->where('user_id', $user->id)
                ->where('status', 'draft')
                ->get();

            if ($checklists->isEmpty()) {
                return response()->json([
                    'message' => 'No draft checklists found to publish'
                ], 404);
            }

            // Publish all draft checklists
            $publishedCount = 0;
            $publishedIds = [];

            foreach ($checklists as $checklist) {
                $checklist->update([
                    'status' => 'published',
                    'published_at' => now()
                ]);
                $publishedCount++;
                $publishedIds[] = $checklist->id;
            }

            // Log activity
            activity_log('bulk_publish', "User published {$publishedCount} draft checklists");

            return response()->json([
                'message' => "{$publishedCount} draft berhasil dipublikasikan",
                'data' => [
                    'published_count' => $publishedCount,
                    'published_ids' => $publishedIds,
                    'skipped_count' => count($ids) - $publishedCount
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mempublikasikan draft',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export user's checklists to a CSV file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        try {
            $user = Auth::user();
            $checklists = $user->checklists()->with('faunas')->latest('tanggal')->get();

            if ($checklists->isEmpty()) {
                return response()->json(['message' => 'Anda tidak memiliki laporan untuk diekspor.'], 404);
            }

            $csvHeader = [
                'ID Laporan',
                'Tipe',
                'Status',
                'Tanggal',
                'Lokasi',
                'Latitude',
                'Longitude',
                'ID Spesies',
                'Nama Spesies',
                'Nama Latin',
                'Jumlah',
            ];

            // Gunakan output buffering untuk membuat file CSV
            ob_start();
            
            // Tambahkan BOM untuk UTF-8 (membantu Excel membuka file dengan benar)
            echo "\xEF\xBB\xBF";
            
            // Buat file handler untuk output
            $output = fopen('php://output', 'w');
            
            // Tulis header CSV
            fputcsv($output, $csvHeader);
            
            // Tulis data checklist
            foreach ($checklists as $checklist) {
                // Format tanggal dengan benar
                $tanggal = $checklist->tanggal instanceof \Carbon\Carbon 
                    ? $checklist->tanggal->format('Y-m-d') 
                    : $checklist->tanggal;
                
                if ($checklist->faunas->isEmpty()) {
                    // Tulis baris untuk checklist tanpa fauna
                    fputcsv($output, [
                        $checklist->id,
                        $checklist->type,
                        $checklist->status,
                        $tanggal,
                        $checklist->nama_lokasi ?? '',
                        $checklist->latitude ?? '',
                        $checklist->longitude ?? '',
                        'N/A',
                        'N/A',
                        'N/A',
                        0,
                    ]);
                } else {
                    // Tulis baris untuk setiap fauna dalam checklist
                    foreach ($checklist->faunas as $fauna) {
                        fputcsv($output, [
                            $checklist->id,
                            $checklist->type,
                            $checklist->status,
                            $tanggal,
                            $checklist->nama_lokasi ?? '',
                            $checklist->latitude ?? '',
                            $checklist->longitude ?? '',
                            $fauna->id,
                            $fauna->nama_spesies ?? '',
                            $fauna->nama_latin ?? '',
                            $fauna->jumlah,
                        ]);
                    }
                }
            }
            
            fclose($output);
            $csvContent = ob_get_clean();
            
            $fileName = 'akar_reports_' . date('Y-m-d') . '.csv';
            
            // Log aktivitas
            activity_log('export', 'User mengekspor data checklist ke CSV');
            
            return response($csvContent, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
        } catch (\Exception $e) {
            \Log::error('CSV Export Error: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat mengekspor data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check for priority fauna observations in the checklist
     */
    private function checkPriorityFaunaObservations(Checklist $checklist, array $data)
    {
        try {
            \Log::info('[ChecklistController] Checking for priority fauna observations', [
                'checklist_id' => $checklist->id,
                'species_count' => count($data['species_list'])
            ]);

            foreach ($data['species_list'] as $speciesIndex => $speciesData) {
                $scientificName = $speciesData['scientificName'] ?? '';
                $speciesName = $speciesData['speciesName'] ?? '';
                
                if (empty($scientificName)) {
                    continue;
                }

                // Check if this species is in priority fauna list
                $priorityFauna = PriorityFauna::where('scientific_name', 'LIKE', '%' . $scientificName . '%')
                    ->orWhere('taxa_name', 'LIKE', '%' . $scientificName . '%')
                    ->orWhere('common_name', 'LIKE', '%' . $speciesName . '%')
                    ->where('is_monitored', true)
                    ->first();

                if ($priorityFauna) {
                    \Log::info('[ChecklistController] Priority fauna detected!', [
                        'priority_fauna_id' => $priorityFauna->id,
                        'scientific_name' => $scientificName,
                        'species_name' => $speciesName
                    ]);

                    // Get photos for this species
                    $speciesPhotos = [];
                    $photoCount = $speciesData['photoCount'] ?? 0;
                    
                    if ($photoCount > 0) {
                        // Get photos from checklist images
                        $images = $checklist->images()
                            ->where('fauna_id', $speciesData['fauna_id'] ?? null)
                            ->get();
                        
                        foreach ($images as $image) {
                            $speciesPhotos[] = $image->image_path;
                        }
                    }

                    // Create priority fauna observation record
                    PriorityFaunaObservation::create([
                        'priority_fauna_id' => $priorityFauna->id,
                        'checklist_id' => $checklist->id,
                        'user_id' => $checklist->user_id,
                        'scientific_name' => $scientificName,
                        'common_name' => $speciesName,
                        'individual_count' => $speciesData['quantity'] ?? 1,
                        'photos' => $speciesPhotos,
                        'latitude' => $checklist->latitude,
                        'longitude' => $checklist->longitude,
                        'location_name' => $checklist->nama_lokasi,
                        'status' => 'new',
                        'observed_at' => $checklist->tanggal,
                    ]);

                    \Log::info('[ChecklistController] Priority fauna observation created successfully', [
                        'priority_fauna_id' => $priorityFauna->id,
                        'checklist_id' => $checklist->id,
                        'user_id' => $checklist->user_id
                    ]);
                }
            }

        } catch (\Exception $e) {
            \Log::error('[ChecklistController] Error checking priority fauna observations: ' . $e->getMessage(), [
                'checklist_id' => $checklist->id,
                'trace' => $e->getTraceAsString()
            ]);
            // Don't fail the checklist creation if priority fauna checking fails
        }
    }
}
