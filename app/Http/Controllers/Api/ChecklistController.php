<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Checklist;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Encoders\JpegEncoder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\ChecklistFauna;
use App\Models\ChecklistImage;

class ChecklistController extends Controller
{
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

        $checklists = $query->latest('tanggal')->get();

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
    public function show(Checklist $checklist)
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
            'faunas' => $checklist->faunas->map(function ($fauna) {
                return [
                    'id' => $fauna->id,
                    'speciesName' => $fauna->nama_spesies ?? 'N/A',
                    'scientificName' => $fauna->nama_latin ?? 'N/A',
                    'fauna_id' => $fauna->fauna_id,
                    'ringed' => $fauna->cincin ? 'Ya' : 'Tidak',
                    'imageUrl' => $fauna->fauna->image_url ?? null,
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
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
            'admin_data.location' => 'required|string|max:255',
            'admin_data.latitude' => 'nullable|numeric|between:-90,90',
            'admin_data.longitude' => 'nullable|numeric|between:-180,180',
            'admin_data.notes' => 'nullable|string',
            'species_list' => 'required|array|min:1',
            'species_list.*.speciesName' => 'required|string|max:255',
            'species_list.*.scientificName' => 'required|string|max:255',
            'species_list.*.fauna_id' => 'nullable|integer',
            'species_list.*.quantity' => 'required|integer|min:1',
            'species_list.*.photoCount' => 'required|integer|min:0',
            'species_list.*.tagging_status' => 'required_unless:type,perburuan|boolean',
            'species_list.*.hunting_status' => 'required_if:type,perburuan|nullable|string|in:hidup,mati',
        ]);

        if ($dataValidator->fails()) {
            return response()->json($dataValidator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $status = isset($data['status']) && in_array($data['status'], ['published', 'draft']) ? $data['status'] : 'draft';

            $checklist = Checklist::create([
                'user_id' => Auth::id(),
                'type' => $data['type'],
                'is_completed' => $data['is_completed'],
                'status' => $status,
                'tanggal' => Carbon::parse($data['admin_data']['date']),
                'nama_lokasi' => $data['admin_data']['location'],
                'latitude' => $data['admin_data']['latitude'] ?? null,
                'longitude' => $data['admin_data']['longitude'] ?? null,
                'catatan' => $data['admin_data']['notes'] ?? null,
                'nama_event' => $data['admin_data']['eventName'] ?? null,
                'teknik_berburu' => $data['admin_data']['huntingTool'] ?? null,
            ]);

            $photos = $request->file('photos') ?? [];
            $photoIndex = 0;

            foreach ($data['species_list'] as $speciesData) {
                $checklist->faunas()->create([
                    'nama_spesies' => $speciesData['speciesName'],
                    'nama_latin' => $speciesData['scientificName'],
                    'fauna_id' => $speciesData['fauna_id'] ?? null,
                    'jumlah' => $speciesData['quantity'],
                    'cincin' => $speciesData['tagging_status'] ?? false,
                    'tagging' => $speciesData['tagging_status'] ?? false,
                    'status_buruan' => $speciesData['hunting_status'] ?? null,
                    'catatan' => $speciesData['notes'] ?? null,
                ]);

                $photoCountForSpecies = $speciesData['photoCount'];
                $speciesPhotos = array_slice($photos, $photoIndex, $photoCountForSpecies);

                foreach ($speciesPhotos as $photo) {
                    $filename = uniqid() . '.jpg';
                                                                                $image = Image::read($photo)->resize(1280, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                                        $compressedImage = $image->encode(new JpegEncoder(quality: 20));

                    $path = 'checklist_images/' . $filename;
                    Storage::disk('public')->put($path, (string) $compressedImage);

                    $checklist->images()->create([
                        'image_path' => $path,
                        'fauna_id' => $speciesData['fauna_id'] ?? null,
                    ]);
                }

                $photoIndex += $photoCountForSpecies;
            }

            DB::commit();

            return response()->json(['message' => 'Checklist created successfully', 'checklist_id' => $checklist->id], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create checklist', 'error' => $e->getMessage()], 500);
        }
    }
}
