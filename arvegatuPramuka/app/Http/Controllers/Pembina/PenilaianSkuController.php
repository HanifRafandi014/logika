<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PenilaianSku;
use App\Models\ManajemenSku;
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PenilaianSkuController extends Controller
{
    public function index()
    {
        $pembinaId = null;
        if (Auth::check() && Auth::user()->role === 'pembina') {
            $pembina = Auth::user()->pembina;
            if ($pembina) {
                $pembinaId = $pembina->id;
            } else {
                return redirect()->route('dashboard')->with('error', 'Data pembina Anda tidak ditemukan.');
            }
        } else {
            return redirect()->route('login')->with('error', 'Anda harus login sebagai pembina.');
        }

        $totalSkuItemsByTingkatan = ManajemenSku::select('tingkatan', DB::raw('count(*) as total_items'))
                                                 ->groupBy('tingkatan')
                                                 ->pluck('total_items', 'tingkatan');

        $penilaianSkus = PenilaianSku::where('pembina_id', $pembinaId)
                                      ->with('siswa', 'pembina', 'manajemen_sku')
                                      ->get();

        $penilaianSkusGrouped = $penilaianSkus->groupBy(function ($item) {
            return $item->siswa_id . '-' . $item->tingkatan;
        })->map(function ($group) use ($totalSkuItemsByTingkatan) {
            $siswaId = $group->first()->siswa_id;
            $tingkatan = $group->first()->tingkatan;

            $checkedCount = $group->where('status', true)->count();
            $totalPossibleItems = $totalSkuItemsByTingkatan->get($tingkatan, 0);

            $overallStatus = ($totalPossibleItems > 0 && $checkedCount === $totalPossibleItems);

            return (object)[
                'siswa_id' => $siswaId,
                'siswa_nama' => $group->first()->siswa->nama ?? 'N/A',
                'pembina_nama' => $group->first()->pembina->nama ?? 'N/A',
                'tingkatan' => $tingkatan,
                'overall_status' => $overallStatus, // This is correctly calculated for display in index
                'last_assessment_date' => $group->max('tanggal'),
            ];
        })->values();

        return view('pembina.nilai_sku.index', compact('penilaianSkusGrouped'));
    }

    public function create()
    {
        $siswas = Siswa::all();
        $tingkatans = ['ramu', 'rakit', 'terap'];

        return view('pembina.nilai_sku.create', compact('siswas', 'tingkatans'));
    }

    public function getSkuItemsByTingkatan(Request $request)
    {
        $request->validate([
            'tingkatan' => 'required|string|in:ramu,rakit,terap',
        ]);

        $tingkatan = $request->input('tingkatan');
        $skuItems = ManajemenSku::where('tingkatan', $tingkatan)->get();

        return response()->json($skuItems);
    }

    public function store(Request $request)
    {
        // Removed 'overall_status' from validation as it's not a direct column in PenilaianSku
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tingkatan' => 'required|string|in:ramu,rakit,terap',
            'assessment_date' => 'required|date',
            'checked_sku_items' => 'array',
            'checked_sku_items.*' => 'exists:manajemen_skus,id',
        ]);

        $pembinaId = null;
        if (Auth::check() && Auth::user()->role === 'pembina') {
            $pembina = Auth::user()->pembina;
            if ($pembina) {
                $pembinaId = $pembina->id;
            } else {
                return redirect()->back()->withInput()->withErrors(['pembina_id' => 'Pembina tidak ditemukan.']);
            }
        } else {
            return redirect()->back()->withInput()->withErrors(['auth' => 'Anda harus login sebagai pembina.']);
        }

        $siswaId = $validatedData['siswa_id'];
        $tingkatan = $validatedData['tingkatan'];
        $assessmentDate = $validatedData['assessment_date'];
        $checkedItems = $validatedData['checked_sku_items'] ?? [];

        // This calculation is useful for debugging or if you want to store overall status elsewhere
        $allRelevantSkuItems = ManajemenSku::where('tingkatan', $tingkatan)->get();
        $totalSkuCount = $allRelevantSkuItems->count();
        $actualOverallStatus = ($totalSkuCount > 0 && count($checkedItems) === $totalSkuCount);

        DB::beginTransaction();
        try {
            // Delete existing assessments for this student, tingkatan, and pembina
            // This ensures we have a clean slate before inserting the updated assessments
            PenilaianSku::where('siswa_id', $siswaId)
                        ->where('tingkatan', $tingkatan)
                        ->where('pembina_id', $pembinaId)
                        ->delete();

            // Insert new records for all relevant SKU items
            foreach ($allRelevantSkuItems as $skuItem) {
                $status = in_array($skuItem->id, $checkedItems); // True if checked, false if not

                PenilaianSku::create([
                    'siswa_id' => $siswaId,
                    'pembina_id' => $pembinaId,
                    'manajemen_sku_id' => $skuItem->id,
                    'status' => $status, // This is the individual item status
                    'tanggal' => $assessmentDate,
                    'tingkatan' => $tingkatan,
                    // Removed 'overall_status' from here as it's not in your schema
                ]);
            }

            DB::commit();
            return redirect()->route('nilai_sku.index')->with('success', 'Penilaian SKU berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing Penilaian SKU: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal menyimpan penilaian SKU. Silakan coba lagi.']);
        }
    }

    public function edit(Request $request, $siswa_id, $tingkatan)
    {
        $pembinaId = null;
        if (Auth::check() && Auth::user()->role === 'pembina') {
            $pembina = Auth::user()->pembina;
            if ($pembina) {
                $pembinaId = $pembina->id;
            }
        }

        if (!$pembinaId) {
            return redirect()->route('nilai_sku.index')->with('error', 'Anda tidak memiliki izin untuk mengedit penilaian ini.');
        }

        $siswa = Siswa::findOrFail($siswa_id);
        $tingkatans = ['ramu', 'rakit', 'terap'];
        $siswas = Siswa::all();

        $skuItemsForTingkatan = ManajemenSku::where('tingkatan', $tingkatan)->get();

        $existingAssessments = PenilaianSku::where('siswa_id', $siswa_id)
                                           ->where('tingkatan', $tingkatan)
                                           ->where('pembina_id', $pembinaId)
                                           ->whereIn('manajemen_sku_id', $skuItemsForTingkatan->pluck('id'))
                                           ->get()
                                           ->keyBy('manajemen_sku_id');

        $penilaianSku = PenilaianSku::where('siswa_id', $siswa_id)
                                     ->where('tingkatan', $tingkatan)
                                     ->where('pembina_id', $pembinaId)
                                     ->orderBy('tanggal', 'desc')
                                     ->first();

        if (!$penilaianSku) {
            $penilaianSku = new PenilaianSku();
            $penilaianSku->siswa_id = $siswa_id;
            $penilaianSku->tingkatan = $tingkatan;
            $penilaianSku->tanggal = date('Y-m-d');
        }

        return view('pembina.nilai_sku.edit', compact(
            'penilaianSku',
            'siswa',
            'siswas',
            'tingkatans',
            'tingkatan',
            'skuItemsForTingkatan',
            'existingAssessments'
        ));
    }

    public function update(Request $request, $siswa_id_route, $tingkatan_route)
    {
        // Removed 'overall_status' from validation as it's not a direct column in PenilaianSku
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tingkatan' => 'required|string|in:ramu,rakit,terap',
            'assessment_date' => 'required|date',
            'checked_sku_items' => 'array',
            'checked_sku_items.*' => 'exists:manajemen_skus,id',
        ]);

        $pembinaId = null;
        if (Auth::check() && Auth::user()->role === 'pembina') {
            $pembina = Auth::user()->pembina;
            if ($pembina) {
                $pembinaId = $pembina->id;
            }
        }

        if (!$pembinaId) {
            return redirect()->route('nilai_sku.index')->with('error', 'Anda tidak memiliki izin untuk memperbarui penilaian ini.');
        }

        $siswaId = $validatedData['siswa_id'];
        $tingkatanSubmitted = $validatedData['tingkatan'];
        $assessmentDate = $validatedData['assessment_date'];
        $checkedItems = $validatedData['checked_sku_items'] ?? [];

        $allRelevantSkuItems = ManajemenSku::where('tingkatan', $tingkatanSubmitted)->get();
        $totalSkuCount = $allRelevantSkuItems->count();
        $actualOverallStatus = ($totalSkuCount > 0 && count($checkedItems) === $totalSkuCount);

        DB::beginTransaction();
        try {
            // Delete all previous assessments for this student, tingkatan, and pembina
            PenilaianSku::where('siswa_id', $siswaId)
                        ->where('tingkatan', $tingkatanSubmitted)
                        ->where('pembina_id', $pembinaId)
                        ->delete();

            // Insert new records based on checked status
            foreach ($allRelevantSkuItems as $skuItem) {
                $status = in_array($skuItem->id, $checkedItems);

                PenilaianSku::create([
                    'siswa_id' => $siswaId,
                    'pembina_id' => $pembinaId,
                    'manajemen_sku_id' => $skuItem->id,
                    'status' => $status,
                    'tanggal' => $assessmentDate,
                    'tingkatan' => $tingkatanSubmitted,
                    // Removed 'overall_status' from here as it's not in your schema
                ]);
            }

            DB::commit();
            return redirect()->route('nilai_sku.index')->with('success', 'Penilaian SKU berhasil diubah!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating Penilaian SKU: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal mengubah penilaian SKU. Silakan coba lagi.']);
        }
    }

    public function destroy(Request $request, $siswa_id, $tingkatan)
    {
        $pembinaId = null;
        if (Auth::check() && Auth::user()->role === 'pembina') {
            $pembina = Auth::user()->pembina;
            if ($pembina) {
                $pembinaId = $pembina->id;
            }
        }

        if (!$pembinaId) {
            return redirect()->route('nilai_sku.index')->with('error', 'Anda tidak memiliki izin untuk menghapus penilaian ini.');
        }

        try {
            PenilaianSku::where('siswa_id', $siswa_id)
                        ->where('tingkatan', $tingkatan)
                        ->where('pembina_id', $pembinaId)
                        ->delete();

            return redirect()->route('nilai_sku.index')->with('success', 'Semua penilaian SKU untuk siswa ini pada tingkatan ' . ucfirst($tingkatan) . ' berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting Penilaian SKU group: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus penilaian SKU. Silakan coba lagi.');
        }
    }
}