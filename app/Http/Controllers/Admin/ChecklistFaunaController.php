<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistFauna;
use App\Models\Taxa;
use App\Models\TaxaLocal;
use Illuminate\Http\Request;

class ChecklistFaunaController extends Controller
{
    /**
     * Mencari taxa berdasarkan ID fauna
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function findTaxa($id)
    {
        try {
            $fauna = ChecklistFauna::findOrFail($id);
            
            if (!$fauna->fauna_id) {
                return redirect()->back()->with('error', 'Fauna ini tidak memiliki referensi ke taxa');
            }
            
            // Cek apakah taxa ada di database lokal
            $localTaxa = TaxaLocal::find($fauna->fauna_id);
            
            if ($localTaxa) {
                return redirect()->route('admin.taxas.show', $localTaxa->id);
            }
            
            // Cek apakah taxa ada di database amaturalist
            $amaturalistTaxa = Taxa::find($fauna->fauna_id);
            
            if ($amaturalistTaxa) {
                // Import taxa ke database lokal
                TaxaLocal::updateOrCreate(
                    ['id' => $amaturalistTaxa->id],
                    [
                        'kingdom' => $amaturalistTaxa->kingdom,
                        'phylum' => $amaturalistTaxa->phylum,
                        'class' => $amaturalistTaxa->class,
                        'order' => $amaturalistTaxa->order,
                        'family' => $amaturalistTaxa->family,
                        'genus' => $amaturalistTaxa->genus,
                        'species' => $amaturalistTaxa->species,
                        'subspecies' => $amaturalistTaxa->subspecies,
                        'common_name' => $amaturalistTaxa->common_name,
                        'local_name' => $amaturalistTaxa->local_name,
                        'scientific_name' => $amaturalistTaxa->scientific_name,
                        'author' => $amaturalistTaxa->author,
                        'rank' => $amaturalistTaxa->rank,
                        'taxonomic_status' => $amaturalistTaxa->taxonomic_status,
                        'iucn_status' => $amaturalistTaxa->iucn_status,
                        'iucn_criteria' => $amaturalistTaxa->iucn_criteria,
                        'cites_status' => $amaturalistTaxa->cites_status,
                        'cites_source' => $amaturalistTaxa->cites_source,
                        'cites_listing_date' => $amaturalistTaxa->cites_listing_date,
                        'image_url' => $amaturalistTaxa->image_url,
                        'description' => $amaturalistTaxa->description,
                        'updated_at' => now(),
                    ]
                );
                
                return redirect()->route('admin.taxas.show', $amaturalistTaxa->id)
                    ->with('success', 'Taxa berhasil diimpor dari database amaturalist');
            }
            
            return redirect()->back()->with('error', 'Taxa dengan ID ' . $fauna->fauna_id . ' tidak ditemukan');
        } catch (\Exception $e) {
            \Log::error('Error finding taxa for fauna', ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
} 