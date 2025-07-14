<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PenilaianSkk;
use App\Models\ManajemenSkk;
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PenilaianSkkController extends Controller
{
    public function index()
    {
        if (Auth::check() && Auth::user()->role === 'pembina') {
            $pembina = Auth::user()->pembina;
            if (!$pembina) {
                return redirect()->route('dashboard')->with('error', 'Data pembina Anda tidak ditemukan.');
            }
        
            $siswasWithPembinaInfo = Siswa::leftJoin('penilaian_skks', 'siswas.id', '=', 'penilaian_skks.siswa_id')
                                            ->leftJoin('pembinas', 'penilaian_skks.pembina_id', '=', 'pembinas.id')
                                            ->select(
                                                'siswas.id as siswa_id',
                                                'siswas.nama as siswa_nama',
                                                'siswas.nisn',
                                                'siswas.kelas',
                                                DB::raw('MAX(CASE WHEN penilaian_skks.pembina_id IS NOT NULL THEN pembinas.nama ELSE NULL END) as last_pembina_name')
                                            )
                                            ->groupBy('siswas.id', 'siswas.nama', 'siswas.nisn', 'siswas.kelas')
                                            ->get();

            return view('pembina.nilai_skk.index', compact('siswasWithPembinaInfo'));

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
        $selectedJenisSkk = $request->query('jenis_skk');

        $siswas = Siswa::all();
        $tingkatans = ['Purwa', 'Madya', 'Utama'];
        $jenisSkks = ManajemenSkk::distinct()->pluck('jenis_skk');
        $disabledTingkatans = [];

        if ($selectedSiswaId && $selectedJenisSkk) {
            $existingTingkatans = \App\Models\PenilaianSkk::where('siswa_id', $selectedSiswaId)
                                    ->where('jenis_skk', $selectedJenisSkk)
                                    ->select('tingkatan')
                                    ->pluck('tingkatan')
                                    ->toArray();

            // Cek apakah semua tingkatan sudah ada
            if (count($existingTingkatans) >= 3) {
                return redirect()->route('nilai_skk.student_assessments', ['siswa_id' => $selectedSiswaId])
                                ->with('warning', 'Semua tingkatan untuk jenis SKK ini sudah dinilai.');
            }

            // Tentukan tingkatan berikutnya yang belum dicapai (Purwa -> Madya -> Utama)
            foreach ($tingkatans as $index => $tingkat) {
                if (!in_array($tingkat, $existingTingkatans)) {
                    // Semua tingkatan kecuali yang ini akan di-disabled
                    $disabledTingkatans = array_filter($tingkatans, fn($t) => $t !== $tingkat);
                    break;
                }
            }
        }

        return view('pembina.nilai_skk.create', compact(
            'siswas',
            'tingkatans',
            'jenisSkks',
            'disabledTingkatans',
            'selectedSiswaId',
            'selectedSiswaNama',
            'selectedSiswaNisn',
            'selectedSiswaKelas',
            'selectedJenisSkk'
        ));
    }

    public function getSkkItems(Request $request)
    {
        $request->validate([
            'tingkatan' => 'required|string|in:Purwa,Madya,Utama',
            'jenis_skk' => 'required|string',
        ]);

        $tingkatan = $request->input('tingkatan');
        $jenisSkk = $request->input('jenis_skk');

        $skkItems = ManajemenSkk::where('tingkatan', $tingkatan)
                                     ->where('jenis_skk', $jenisSkk)
                                     ->get();

        return response()->json($skkItems);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tingkatan' => 'required|string|in:Purwa,Madya,Utama',
            'jenis_skk' => 'required|string',
            'assessment_date' => 'required|date',
            'checked_skk_items' => 'array',
            'checked_skk_items.*' => 'exists:manajemen_skks,id',
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
        $jenisSkk = $validatedData['jenis_skk'];
        $assessmentDate = $validatedData['assessment_date'];
        $checkedItems = $validatedData['checked_skk_items'] ?? [];
        $buktiPdfPath = null;

        if ($request->hasFile('bukti_pdf')) {
            $file = $request->file('bukti_pdf');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = 'public/bukti_skk_pdfs';
            $file->storeAs($path, $fileName);
            $buktiPdfPath = 'storage/bukti_skk_pdfs/' . $fileName;
        }

        $allRelevantSkkItems = ManajemenSkk::where('tingkatan', $tingkatan)
                                               ->where('jenis_skk', $jenisSkk)
                                               ->get();

        DB::beginTransaction();
        try {
            $existingAssessmentForPdf = PenilaianSkk::where('siswa_id', $siswaId)
                                                     ->where('tingkatan', $tingkatan)
                                                     ->where('jenis_skk', $jenisSkk)
                                                     ->where('pembina_id', $pembinaId)
                                                     ->first();
            
            if ($existingAssessmentForPdf && $existingAssessmentForPdf->bukti_pdf) {
                $oldPdfPath = str_replace('storage/', 'public/', $existingAssessmentForPdf->bukti_pdf);
                if (Storage::exists($oldPdfPath)) {
                    Storage::delete($oldPdfPath);
                }
            }

            PenilaianSkk::where('siswa_id', $siswaId)
                        ->where('tingkatan', $tingkatan)
                        ->where('jenis_skk', $jenisSkk)
                        ->where('pembina_id', $pembinaId)
                        ->delete();

            foreach ($allRelevantSkkItems as $skkItem) {
                $status = in_array($skkItem->id, $checkedItems);

                PenilaianSkk::create([
                    'siswa_id' => $siswaId,
                    'pembina_id' => $pembinaId,
                    'manajemen_skk_id' => $skkItem->id,
                    'status' => $status,
                    'tanggal' => $assessmentDate,
                    'tingkatan' => $tingkatan,
                    'jenis_skk' => $jenisSkk,
                    'bukti_pdf' => $buktiPdfPath,
                ]);
            }

            DB::commit();
            // Redirect back to the student_assessments page after adding
            return redirect()->route('nilai_skk.student_assessments', [
                'siswa_id' => $siswaId
            ])->with('success', 'Penilaian SKK berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($buktiPdfPath && Storage::exists(str_replace('storage/', 'public/', $buktiPdfPath))) {
                Storage::delete(str_replace('storage/', 'public/', $buktiPdfPath));
            }
            Log::error('Error storing Penilaian SKK: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal menyimpan penilaian SKK. Silakan coba lagi.']);
        }
    }

    public function edit(Request $request, $siswa_id, $tingkatan, $jenis_skk)
    {
        $pembinaId = null;
        if (Auth::check() && Auth::user()->role === 'pembina') {
            $pembina = Auth::user()->pembina;
            if ($pembina) {
                $pembinaId = $pembina->id;
            }
        }

        if (!$pembinaId) {
            return redirect()->route('nilai_skk.index')->with('error', 'Anda tidak memiliki izin untuk mengedit penilaian ini.');
        }

        $siswa = Siswa::findOrFail($siswa_id);
        $tingkatans = ['Purwa', 'Madya', 'Utama'];
        $jenisSkks = ManajemenSkk::distinct()->pluck('jenis_skk');

        $skkItemsForTingkatanAndJenisSkk = ManajemenSkk::where('tingkatan', $tingkatan)
                                                         ->where('jenis_skk', $jenis_skk)
                                                         ->get();

        $existingAssessments = PenilaianSkk::where('siswa_id', $siswa_id)
                                               ->where('tingkatan', $tingkatan)
                                               ->where('jenis_skk', $jenis_skk)
                                               ->where('pembina_id', $pembinaId)
                                               ->get();
        
        $penilaianSkk = $existingAssessments->first();

        if (!$penilaianSkk) {
            $penilaianSkk = new PenilaianSkk();
            $penilaianSkk->siswa_id = $siswa_id;
            $penilaianSkk->tingkatan = $tingkatan;
            $penilaianSkk->jenis_skk = $jenis_skk;
            $penilaianSkk->tanggal = date('Y-m-d');
            $penilaianSkk->bukti_pdf = null;
        }

        $existingAssessmentsKeyed = $existingAssessments->keyBy('manajemen_skk_id');

        return view('pembina.nilai_skk.edit', compact(
            'penilaianSkk',
            'siswa',
            'tingkatans',
            'tingkatan',
            'jenis_skk',
            'jenisSkks',
            'skkItemsForTingkatanAndJenisSkk',
            'existingAssessmentsKeyed'
        ));
    }

    public function update(Request $request, $siswa_id_route, $tingkatan_route, $jenis_skk_route)
    {
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tingkatan' => 'required|string|in:Purwa,Madya,Utama',
            'jenis_skk' => 'required|string',
            'assessment_date' => 'required|date',
            'checked_skk_items' => 'array',
            'checked_skk_items.*' => 'exists:manajemen_skks,id',
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
            return redirect()->route('nilai_skk.index')->with('error', 'Anda tidak memiliki izin untuk memperbarui penilaian ini.');
        }

        $siswaId = $validatedData['siswa_id'];
        $tingkatanSubmitted = $validatedData['tingkatan'];
        $jenisSkkSubmitted = $validatedData['jenis_skk'];
        $assessmentDate = $validatedData['assessment_date'];
        $checkedItems = $validatedData['checked_skk_items'] ?? [];
        $buktiPdfPath = null;

        $existingAssessmentForPdf = PenilaianSkk::where('siswa_id', $siswaId)
                                                   ->where('tingkatan', $tingkatanSubmitted)
                                                   ->where('jenis_skk', $jenisSkkSubmitted)
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
            $path = 'public/bukti_skk_pdfs';
            $file->storeAs($path, $fileName);
            $buktiPdfPath = 'storage/bukti_skk_pdfs/' . $fileName;
        } else {
            if (!$request->input('remove_bukti_pdf')) {
                   $buktiPdfPath = $currentBuktiPdf;
            }
        }

        $allRelevantSkkItems = ManajemenSkk::where('tingkatan', $tingkatanSubmitted)
                                               ->where('jenis_skk', $jenisSkkSubmitted)
                                               ->get();

        DB::beginTransaction();
        try {
            PenilaianSkk::where('siswa_id', $siswaId)
                        ->where('tingkatan', $tingkatanSubmitted)
                        ->where('jenis_skk', $jenisSkkSubmitted)
                        ->where('pembina_id', $pembinaId)
                        ->delete();

            foreach ($allRelevantSkkItems as $skkItem) {
                $status = in_array($skkItem->id, $checkedItems);

                PenilaianSkk::create([
                    'siswa_id' => $siswaId,
                    'pembina_id' => $pembinaId,
                    'manajemen_skk_id' => $skkItem->id,
                    'status' => $status,
                    'tanggal' => $assessmentDate,
                    'tingkatan' => $tingkatanSubmitted,
                    'jenis_skk' => $jenisSkkSubmitted,
                    'bukti_pdf' => $buktiPdfPath,
                ]);
            }

            DB::commit();
            return redirect()->route('nilai_skk.student_assessments', [
                'siswa_id' => $siswaId
            ])->with('success', 'Penilaian SKK berhasil diubah!');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->hasFile('bukti_pdf') && $buktiPdfPath && Storage::exists(str_replace('storage/', 'public/', $buktiPdfPath))) {
                Storage::delete(str_replace('storage/', 'public/', $buktiPdfPath));
            }
            Log::error('Error updating Penilaian SKK: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal mengubah penilaian SKK. Silakan coba lagi.']);
        }
    }

    public function show($siswa_id, $tingkatan, $jenis_skk)
    {
        $pembinaId = null;
        if (Auth::check() && Auth::user()->role === 'pembina') {
            $pembina = Auth::user()->pembina;
            if ($pembina) {
                $pembinaId = $pembina->id;
            }
        }

        if (!$pembinaId) {
            return redirect()->route('nilai_skk.index')->with('error', 'Anda tidak memiliki izin untuk melihat penilaian ini.');
        }

        $siswa = Siswa::findOrFail($siswa_id);

        $allSkkItems = ManajemenSkk::where('tingkatan', $tingkatan)
                                           ->where('jenis_skk', $jenis_skk)
                                           ->get();

        $penilaianSkks = PenilaianSkk::where('siswa_id', $siswa_id)
                                           ->where('tingkatan', $tingkatan)
                                           ->where('jenis_skk', $jenis_skk)
                                           ->where('pembina_id', $pembinaId)
                                           ->with('manajemen_skk')
                                           ->get();

        $assessedSkkMap = $penilaianSkks->keyBy('manajemen_skk_id');

        $firstAssessment = $penilaianSkks->first();
        $assessmentDate = $firstAssessment->tanggal ?? null;
        $buktiPdf = $firstAssessment->bukti_pdf ?? null;

        $checkedCount = $penilaianSkks->where('status', true)->count();
        $totalPossibleItems = $allSkkItems->count();
        $progressPercentage = ($totalPossibleItems > 0) ? round(($checkedCount / $totalPossibleItems) * 100, 2) : 0;
        $overallStatus = ($totalPossibleItems > 0 && $checkedCount === $totalPossibleItems);

        return view('pembina.nilai_skk.show', compact(
            'siswa',
            'tingkatan',
            'jenis_skk',
            'allSkkItems',
            'assessedSkkMap',
            'assessmentDate',
            'buktiPdf',
            'checkedCount',
            'totalPossibleItems',
            'progressPercentage',
            'overallStatus'
        ));
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
            return redirect()->route('nilai_skk.index')->with('error', 'Anda tidak memiliki izin untuk melihat penilaian ini.');
        }

        $siswa = Siswa::findOrFail($siswa_id);

        // Fetch all distinct tingkatan and jenis_skk from ManajemenSkk for accurate total counts
        $totalSkkItemsByTingkatanAndJenisSkk = ManajemenSkk::select('tingkatan', 'jenis_skk', DB::raw('count(*) as total_items'))
                                                            ->groupBy('tingkatan', 'jenis_skk')
                                                            ->get()
                                                            ->mapWithKeys(function ($item) {
                                                                return [$item->tingkatan . '-' . $item->jenis_skk => $item->total_items];
                                                            });

        // Fetch SKK assessments for this specific student by the current pembina, grouped.
        $penilaianSkks = PenilaianSkk::where('siswa_id', $siswa_id)
                                          ->where('pembina_id', $pembinaId) // Only assessments by this pembina
                                          ->with('siswa', 'pembina', 'manajemen_skk')
                                          ->get();

        $penilaianSkksGrouped = $penilaianSkks->groupBy(function ($item) {
            return $item->siswa_id . '-' . $item->tingkatan . '-' . $item->jenis_skk;
        })->map(function ($group) use ($totalSkkItemsByTingkatanAndJenisSkk) {
            $siswaId = $group->first()->siswa_id;
            $tingkatan = $group->first()->tingkatan;
            $jenisSkk = $group->first()->jenis_skk;

            $checkedCount = $group->where('status', true)->count();
            $totalPossibleItems = $totalSkkItemsByTingkatanAndJenisSkk->get($tingkatan . '-' . $jenisSkk, 0);

            $overallStatus = ($totalPossibleItems > 0 && $checkedCount === $totalPossibleItems);

            $buktiPdf = $group->first()->bukti_pdf ?? null;

            $progressPercentage = ($totalPossibleItems > 0) ? round(($checkedCount / $totalPossibleItems) * 100, 2) : 0;

            return (object)[
                'siswa_id' => $siswaId,
                'siswa_nama' => $group->first()->siswa->nama ?? 'N/A',
                'pembina_nama' => $group->first()->pembina->nama ?? 'N/A',
                'tingkatan' => $tingkatan,
                'jenis_skk' => $jenisSkk,
                'overall_status' => $overallStatus,
                'last_assessment_date' => $group->max('tanggal'),
                'bukti_pdf' => $buktiPdf,
                'checked_count' => $checkedCount,
                'total_possible_items' => $totalPossibleItems,
                'progress_percentage' => $progressPercentage,
            ];
        })->values();

        return view('pembina.nilai_skk.student_assessments', compact('siswa', 'penilaianSkksGrouped'));
    }

    public function destroy(Request $request, $siswa_id, $tingkatan, $jenis_skk)
    {
        $pembinaId = null;
        if (Auth::check() && Auth::user()->role === 'pembina') {
            $pembina = Auth::user()->pembina;
            if ($pembina) {
                $pembinaId = $pembina->id;
            }
        }

        if (!$pembinaId) {
            return redirect()->route('nilai_skk.index')->with('error', 'Anda tidak memiliki izin untuk menghapus penilaian ini.');
        }

        DB::beginTransaction();
        try {
            $penilaianToDelete = PenilaianSkk::where('siswa_id', $siswa_id)
                                             ->where('tingkatan', $tingkatan)
                                             ->where('jenis_skk', $jenis_skk)
                                             ->where('pembina_id', $pembinaId)
                                             ->first();

            if ($penilaianToDelete && $penilaianToDelete->bukti_pdf) {
                $pdfPath = str_replace('storage/', 'public/', $penilaianToDelete->bukti_pdf);
                if (Storage::exists($pdfPath)) {
                    Storage::delete($pdfPath);
                }
            }

            PenilaianSkk::where('siswa_id', $siswa_id)
                        ->where('tingkatan', $tingkatan)
                        ->where('jenis_skk', $jenis_skk)
                        ->where('pembina_id', $pembinaId)
                        ->delete();

            DB::commit();
            return redirect()->route('nilai_skk.student_assessments', [
                'siswa_id' => $siswa_id
            ])->with('success', 'Semua penilaian SKK untuk siswa ini pada tingkatan ' . ucfirst($tingkatan) . ' dan jenis SKK "' . $jenis_skk . '" berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting Penilaian SKK group: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus penilaian SKK. Silakan coba lagi.');
        }
    }

    /**
     * NEW METHOD: Delete all SKK assessments for a specific student.
     * This is a mass deletion from the main index page.
     *
     * @param  int  $siswa_id
     * @return \Illuminate\Http\RedirectResponse
     */
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
            return redirect()->route('nilai_skk.index')->with('error', 'Anda tidak memiliki izin untuk menghapus penilaian.');
        }

        DB::beginTransaction();
        try {
            // Find all SKK assessments for this student by the current pembina
            $assessmentsToDelete = PenilaianSkk::where('siswa_id', $siswa_id)
                                                 ->where('pembina_id', $pembinaId)
                                                 ->get();

            // Delete associated PDF files
            foreach ($assessmentsToDelete as $assessment) {
                if ($assessment->bukti_pdf) {
                    $pdfPath = str_replace('storage/', 'public/', $assessment->bukti_pdf);
                    if (Storage::exists($pdfPath)) {
                        Storage::delete($pdfPath);
                    }
                }
            }

            // Delete all records for this student by this pembina
            PenilaianSkk::where('siswa_id', $siswa_id)
                        ->where('pembina_id', $pembinaId)
                        ->delete();

            DB::commit();
            return redirect()->route('nilai_skk.index')->with('success', 'Semua penilaian SKK untuk siswa ini berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting all Penilaian SKK for siswa: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus semua penilaian SKK siswa. Silakan coba lagi.');
        }
    }
}