<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PenilaianSku; // Make sure this model is correct
use App\Models\ManajemenSku; // Make sure this model is correct
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PenilaianSkuController extends Controller
{
    public function index()
    {
        if (Auth::check() && Auth::user()->role === 'pembina') {
            $pembina = Auth::user()->pembina;
            if (!$pembina) {
                return redirect()->route('dashboard')->with('error', 'Data pembina Anda tidak ditemukan.');
            }

            // Filter siswa hanya berdasarkan kelas pembina yang login
            $siswasWithPembinaInfo = Siswa::leftJoin('penilaian_skus', 'siswas.id', '=', 'penilaian_skus.siswa_id')
                ->leftJoin('pembinas', 'penilaian_skus.pembina_id', '=', 'pembinas.id')
                ->select(
                    'siswas.id as siswa_id',
                    'siswas.nama as siswa_nama',
                    'siswas.nisn',
                    'siswas.kelas',
                    DB::raw('MAX(CASE WHEN penilaian_skus.pembina_id IS NOT NULL THEN pembinas.nama ELSE NULL END) as last_pembina_name')
                )
                ->where('siswas.kelas', $pembina->kelas) // <- filter di sini
                ->groupBy('siswas.id', 'siswas.nama', 'siswas.nisn', 'siswas.kelas')
                ->get();

            return view('pembina.nilai_sku.index', compact('siswasWithPembinaInfo'));

        } else {
            return redirect()->route('login')->with('error', 'Anda harus login sebagai pembina.');
        }
    }

    public function create(Request $request)
    {
        $selectedSiswaId = $request->query('siswa_id');
        $selectedSiswaNama = $request->query('siswa_nama');
        $selectedSiswaNisn = $request->query('siswa_nisn');
        $selectedSiswaKelas = $request->query('siswa_kelas');

        $siswas = Siswa::all();
        $allTingkatans = ['Ramu', 'Rakit', 'Terap'];
        $disabledTingkatans = [];

        if ($selectedSiswaId) {
            $existingTingkatans = PenilaianSku::where('siswa_id', $selectedSiswaId)
                                    ->select('tingkatan')
                                    ->distinct()
                                    ->pluck('tingkatan')
                                    ->toArray();

            // Menentukan tingkatan berikutnya yang belum ada
            $nextIndex = 0;
            foreach ($allTingkatans as $index => $tingkat) {
                if (!in_array($tingkat, $existingTingkatans)) {
                    $nextIndex = $index;
                    break;
                }
            }

            // Semua tingkatan selain tingkatan yang boleh dinilai di-disable
            foreach ($allTingkatans as $index => $tingkat) {
                if ($index !== $nextIndex) {
                    $disabledTingkatans[] = $tingkat;
                }
            }
        }

        return view('pembina.nilai_sku.create', compact(
            'siswas',
            'allTingkatans',
            'disabledTingkatans',
            'selectedSiswaId',
            'selectedSiswaNama',
            'selectedSiswaNisn',
            'selectedSiswaKelas'
        ));
    }

    public function getSkuItemsByTingkatan(Request $request)
    {
        $request->validate([
            'tingkatan' => 'required|string|in:Ramu,Rakit,Terap',
        ]);

        $tingkatan = $request->input('tingkatan');
        $skuItems = ManajemenSku::where('tingkatan', $tingkatan)->get();

        return response()->json($skuItems);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tingkatan' => 'required|string|in:Ramu,Rakit,Terap',
            'assessment_date' => 'required|date',
            'checked_sku_items' => 'array',
            'checked_sku_items.*' => 'exists:manajemen_skus,id',
            'bukti_pdf' => 'nullable|file|mimes:pdf|max:2048',
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
        $buktiPdfPath = null;

        if ($request->hasFile('bukti_pdf')) {
            $file = $request->file('bukti_pdf');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = 'public/bukti_sku_pdfs';
            $file->storeAs($path, $fileName);
            $buktiPdfPath = 'storage/bukti_sku_pdfs/' . $fileName;
        }

        $allRelevantSkuItems = ManajemenSku::where('tingkatan', $tingkatan)->get();

        DB::beginTransaction();
        try {
            // Check for existing assessment to delete its associated PDF
            $existingAssessmentForPdf = PenilaianSku::where('siswa_id', $siswaId)
                                                     ->where('tingkatan', $tingkatan)
                                                     ->where('pembina_id', $pembinaId)
                                                     ->first();
            
            if ($existingAssessmentForPdf && $existingAssessmentForPdf->bukti_pdf) {
                $oldPdfPath = str_replace('storage/', 'public/', $existingAssessmentForPdf->bukti_pdf);
                if (Storage::exists($oldPdfPath)) {
                    Storage::delete($oldPdfPath);
                }
            }

            // Delete existing assessments for this student, tingkatan, and pembina
            PenilaianSku::where('siswa_id', $siswaId)
                        ->where('tingkatan', $tingkatan)
                        ->where('pembina_id', $pembinaId)
                        ->delete();

            foreach ($allRelevantSkuItems as $skuItem) {
                $status = in_array($skuItem->id, $checkedItems);

                PenilaianSku::create([
                    'siswa_id' => $siswaId,
                    'pembina_id' => $pembinaId,
                    'manajemen_sku_id' => $skuItem->id,
                    'status' => $status,
                    'tanggal' => $assessmentDate,
                    'tingkatan' => $tingkatan,
                    'bukti_pdf' => $buktiPdfPath,
                ]);
            }

            DB::commit();
            // Redirect to the student's SKU assessments page after adding
            return redirect()->route('nilai_sku.student_assessments', ['siswa_id' => $siswaId])->with('success', 'Penilaian SKU berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($buktiPdfPath && Storage::exists(str_replace('storage/', 'public/', $buktiPdfPath))) {
                Storage::delete(str_replace('storage/', 'public/', $buktiPdfPath));
            }
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
        $tingkatans = ['Ramu', 'Rakit', 'Terap'];

        $skuItemsForTingkatan = ManajemenSku::where('tingkatan', $tingkatan)->get();

        $existingAssessments = PenilaianSku::where('siswa_id', $siswa_id)
                                               ->where('tingkatan', $tingkatan)
                                               ->where('pembina_id', $pembinaId)
                                               ->get();
        
        $penilaianSku = $existingAssessments->first();

        if (!$penilaianSku) {
            $penilaianSku = new PenilaianSku();
            $penilaianSku->siswa_id = $siswa_id;
            $penilaianSku->tingkatan = $tingkatan;
            $penilaianSku->tanggal = date('Y-m-d');
            $penilaianSku->bukti_pdf = null;
        }

        $existingAssessmentsKeyed = $existingAssessments->keyBy('manajemen_sku_id');

        return view('pembina.nilai_sku.edit', compact(
            'penilaianSku',
            'siswa',
            'tingkatans',
            'tingkatan',
            'skuItemsForTingkatan',
            'existingAssessmentsKeyed'
        ));
    }

    public function update(Request $request, $siswa_id_route, $tingkatan_route)
    {
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tingkatan' => 'required|string|in:Ramu,Rakit,Terap',
            'assessment_date' => 'required|date',
            'checked_sku_items' => 'array',
            'checked_sku_items.*' => 'exists:manajemen_skus,id',
            'bukti_pdf' => 'nullable|file|mimes:pdf|max:2048',
            'remove_bukti_pdf' => 'boolean',
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
        $buktiPdfPath = null;

        $existingAssessmentForPdf = PenilaianSku::where('siswa_id', $siswaId)
                                                   ->where('tingkatan', $tingkatanSubmitted)
                                                   ->where('pembina_id', $pembinaId)
                                                   ->first();
        
        $currentBuktiPdf = $existingAssessmentForPdf ? $existingAssessmentForPdf->bukti_pdf : null;

        if ($request->input('remove_bukti_pdf') && $currentBuktiPdf) {
            $oldPdfPath = str_replace('storage/', 'public/', $currentBuktiPdf);
            if (Storage::exists($oldPdfPath)) {
                Storage::delete($oldPdfPath);
            }
            $buktiPdfPath = null;
        }

        if ($request->hasFile('bukti_pdf')) {
            if ($currentBuktiPdf) {
                $oldPdfPath = str_replace('storage/', 'public/', $currentBuktiPdf);
                if (Storage::exists($oldPdfPath)) {
                    Storage::delete($oldPdfPath);
                }
            }
            $file = $request->file('bukti_pdf');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = 'public/bukti_sku_pdfs';
            $file->storeAs($path, $fileName);
            $buktiPdfPath = 'storage/bukti_sku_pdfs/' . $fileName;
        } else {
            if (!$request->input('remove_bukti_pdf')) {
                   $buktiPdfPath = $currentBuktiPdf;
            }
        }

        $allRelevantSkuItems = ManajemenSku::where('tingkatan', $tingkatanSubmitted)->get();

        DB::beginTransaction();
        try {
            PenilaianSku::where('siswa_id', $siswaId)
                        ->where('tingkatan', $tingkatanSubmitted)
                        ->where('pembina_id', $pembinaId)
                        ->delete();

            foreach ($allRelevantSkuItems as $skuItem) {
                $status = in_array($skuItem->id, $checkedItems);

                PenilaianSku::create([
                    'siswa_id' => $siswaId,
                    'pembina_id' => $pembinaId,
                    'manajemen_sku_id' => $skuItem->id,
                    'status' => $status,
                    'tanggal' => $assessmentDate,
                    'tingkatan' => $tingkatanSubmitted,
                    'bukti_pdf' => $buktiPdfPath,
                ]);
            }

            DB::commit();
            // Redirect back to the student's SKU assessments page after update
            return redirect()->route('nilai_sku.student_assessments', ['siswa_id' => $siswaId])->with('success', 'Penilaian SKU berhasil diubah!');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->hasFile('bukti_pdf') && $buktiPdfPath && Storage::exists(str_replace('storage/', 'public/', $buktiPdfPath))) {
                Storage::delete(str_replace('storage/', 'public/', $buktiPdfPath));
            }
            Log::error('Error updating Penilaian SKU: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal mengubah penilaian SKU. Silakan coba lagi.']);
        }
    }

    public function show($siswa_id, $tingkatan)
    {
        $pembinaId = null;
        if (Auth::check() && Auth::user()->role === 'pembina') {
            $pembina = Auth::user()->pembina;
            if ($pembina) {
                $pembinaId = $pembina->id;
            }
        }

        if (!$pembinaId) {
            return redirect()->route('nilai_sku.index')->with('error', 'Anda tidak memiliki izin untuk melihat penilaian ini.');
        }

        $siswa = Siswa::findOrFail($siswa_id);

        // Fetch all SKU items for the specified tingkatan
        $allSkuItems = ManajemenSku::where('tingkatan', $tingkatan)->get();

        // Fetch the assessment records for this student, tingkatan, and pembina
        $penilaianSkus = PenilaianSku::where('siswa_id', $siswa_id)
                                           ->where('tingkatan', $tingkatan)
                                           ->where('pembina_id', $pembinaId)
                                           ->with('manajemen_sku')
                                           ->get();

        // Create a map of assessed SKU items for easy lookup
        $assessedSkuMap = $penilaianSkus->keyBy('manajemen_sku_id');

        // Get the overall assessment date and bukti_pdf (assuming they are consistent across the grouped entries)
        $firstAssessment = $penilaianSkus->first();
        $assessmentDate = $firstAssessment->tanggal ?? null;
        $buktiPdf = $firstAssessment->bukti_pdf ?? null;

        // Calculate progress and overall status for display in the show view
        $checkedCount = $penilaianSkus->where('status', true)->count();
        $totalPossibleItems = $allSkuItems->count(); // Total items for this tingkatan
        $progressPercentage = ($totalPossibleItems > 0) ? round(($checkedCount / $totalPossibleItems) * 100, 2) : 0;
        $overallStatus = ($totalPossibleItems > 0 && $checkedCount === $totalPossibleItems);

        return view('pembina.nilai_sku.show', compact('siswa', 'tingkatan', 'allSkuItems', 'assessedSkuMap', 'assessmentDate', 'buktiPdf', 'checkedCount', 'totalPossibleItems', 'progressPercentage', 'overallStatus'));
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

        DB::beginTransaction();
        try {
            $penilaianToDelete = PenilaianSku::where('siswa_id', $siswa_id)
                                             ->where('tingkatan', $tingkatan)
                                             ->where('pembina_id', $pembinaId)
                                             ->first();

            if ($penilaianToDelete && $penilaianToDelete->bukti_pdf) {
                $pdfPath = str_replace('storage/', 'public/', $penilaianToDelete->bukti_pdf);
                if (Storage::exists($pdfPath)) {
                    Storage::delete($pdfPath);
                }
            }

            PenilaianSku::where('siswa_id', $siswa_id)
                        ->where('tingkatan', $tingkatan)
                        ->where('pembina_id', $pembinaId)
                        ->delete();

            DB::commit();
            // Redirect back to the student's SKU assessments page after deletion
            return redirect()->route('nilai_sku.student_assessments', ['siswa_id' => $siswa_id])->with('success', 'Semua penilaian SKU untuk siswa ini pada tingkatan ' . ucfirst($tingkatan) . ' berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting Penilaian SKU group: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus penilaian SKU. Silakan coba lagi.');
        }
    }

    public function studentAssessments($siswa_id)
    {
        $pembinaId = null;
        if (Auth::check() && Auth::user()->role === 'pembina') {
            $pembina = Auth::user()->pembina;
            if ($pembina) {
                $pembinaId = $pembina->id;
            }
        }

        if (!$pembinaId) {
            return redirect()->route('nilai_sku.index')->with('error', 'Anda tidak memiliki izin untuk melihat penilaian ini.');
        }

        $siswa = Siswa::findOrFail($siswa_id);

        // Fetch all distinct tingkatan from ManajemenSku for accurate total counts
        $totalSkuItemsByTingkatan = ManajemenSku::select('tingkatan', DB::raw('count(*) as total_items'))
                                                    ->groupBy('tingkatan')
                                                    ->pluck('total_items', 'tingkatan');

        // Fetch SKU assessments for this specific student by the current pembina, grouped by tingkatan.
        $penilaianSkus = PenilaianSku::where('siswa_id', $siswa_id)
                                          ->where('pembina_id', $pembinaId) // Only assessments by this pembina
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

            $buktiPdf = $group->first()->bukti_pdf ?? null;

            $progressPercentage = ($totalPossibleItems > 0) ? round(($checkedCount / $totalPossibleItems) * 100, 2) : 0;

            return (object)[
                'siswa_id' => $siswaId,
                'siswa_nama' => $group->first()->siswa->nama ?? 'N/A',
                'pembina_nama' => $group->first()->pembina->nama ?? 'N/A',
                'tingkatan' => $tingkatan,
                'overall_status' => $overallStatus,
                'last_assessment_date' => $group->max('tanggal'),
                'bukti_pdf' => $buktiPdf,
                'checked_count' => $checkedCount,
                'total_possible_items' => $totalPossibleItems,
                'progress_percentage' => $progressPercentage,
            ];
        })->values();

        return view('pembina.nilai_sku.student_assessments', compact('siswa', 'penilaianSkusGrouped'));
    }

    public function deleteAllForSiswa($siswa_id)
    {
        $pembinaId = null;
        if (Auth::check() && Auth::user()->role === 'pembina') {
            $pembina = Auth::user()->pembina;
            if ($pembina) {
                $pembinaId = $pembina->id;
            }
        }

        if (!$pembinaId) {
            return redirect()->route('nilai_sku.index')->with('error', 'Anda tidak memiliki izin untuk menghapus penilaian.');
        }

        DB::beginTransaction();
        try {
            $assessmentsToDelete = PenilaianSku::where('siswa_id', $siswa_id)
                                                 ->where('pembina_id', $pembinaId)
                                                 ->get();

            foreach ($assessmentsToDelete as $assessment) {
                if ($assessment->bukti_pdf) {
                    $pdfPath = str_replace('storage/', 'public/', $assessment->bukti_pdf);
                    if (Storage::exists($pdfPath)) {
                        Storage::delete($pdfPath);
                    }
                }
            }

            PenilaianSku::where('siswa_id', $siswa_id)
                        ->where('pembina_id', $pembinaId)
                        ->delete();

            DB::commit();
            return redirect()->route('nilai_sku.index')->with('success', 'Semua penilaian SKU untuk siswa ini berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting all Penilaian SKU for siswa: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus semua penilaian SKU siswa. Silakan coba lagi.');
        }
    }
}