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

class PenilaianSkkController extends Controller
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

        // Fetch all distinct tingkatan and jenis_skk from ManajemenSkk for accurate total counts
        $totalSkkItemsByTingkatanAndJenisSkk = ManajemenSkk::select('tingkatan', 'jenis_skk', DB::raw('count(*) as total_items'))
                                                            ->groupBy('tingkatan', 'jenis_skk')
                                                            ->get()
                                                            ->mapWithKeys(function ($item) {
                                                                return [$item->tingkatan . '-' . $item->jenis_skk => $item->total_items];
                                                            });

        $penilaianSkks = PenilaianSkk::where('pembina_id', $pembinaId)
                                     ->with('siswa', 'pembina', 'manajemen_skk')
                                     ->get();

        $penilaianSkksGrouped = $penilaianSkks->groupBy(function ($item) {
            // Group by siswa_id, tingkatan, AND jenis_skk for unique assessment sets
            return $item->siswa_id . '-' . $item->tingkatan . '-' . $item->jenis_skk;
        })->map(function ($group) use ($totalSkkItemsByTingkatanAndJenisSkk) {
            $siswaId = $group->first()->siswa_id;
            $tingkatan = $group->first()->tingkatan;
            $jenisSkk = $group->first()->jenis_skk; // Get jenis_skk from the group

            $checkedCount = $group->where('status', true)->count();
            $totalPossibleItems = $totalSkkItemsByTingkatanAndJenisSkk->get($tingkatan . '-' . $jenisSkk, 0);

            $overallStatus = ($totalPossibleItems > 0 && $checkedCount === $totalPossibleItems);

            return (object)[
                'siswa_id' => $siswaId,
                'siswa_nama' => $group->first()->siswa->nama ?? 'N/A',
                'pembina_nama' => $group->first()->pembina->nama ?? 'N/A',
                'tingkatan' => $tingkatan,
                'jenis_skk' => $jenisSkk, // Include jenis_skk in the grouped data
                'overall_status' => $overallStatus,
                'last_assessment_date' => $group->max('tanggal'),
            ];
        })->values();

        return view('pembina.nilai_skk.index', compact('penilaianSkksGrouped'));
    }

    public function create()
    {
        $siswas = Siswa::all();
        $tingkatans = ['purwa', 'madya', 'utama'];
        // Get unique jenis_skk from ManajemenSkk
        $jenisSkks = ManajemenSkk::distinct()->pluck('jenis_skk');

        return view('pembina.nilai_skk.create', compact('siswas', 'tingkatans', 'jenisSkks'));
    }

    public function getSkkItems(Request $request)
    {
        $request->validate([
            'tingkatan' => 'required|string|in:purwa,madya,utama',
            'jenis_skk' => 'required|string', // Validate jenis_skk
        ]);

        $tingkatan = $request->input('tingkatan');
        $jenisSkk = $request->input('jenis_skk');

        $skkItems = ManajemenSkk::where('tingkatan', $tingkatan)
                                  ->where('jenis_skk', $jenisSkk) // Filter by jenis_skk
                                  ->get();

        return response()->json($skkItems);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tingkatan' => 'required|string|in:purwa,madya,utama',
            'jenis_skk' => 'required|string', // Validate jenis_skk
            'assessment_date' => 'required|date',
            'checked_skk_items' => 'array',
            'checked_skk_items.*' => 'exists:manajemen_skks,id',
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
        $jenisSkk = $validatedData['jenis_skk']; // Get jenis_skk from validated data
        $assessmentDate = $validatedData['assessment_date'];
        $checkedItems = $validatedData['checked_skk_items'] ?? [];

        $allRelevantSkkItems = ManajemenSkk::where('tingkatan', $tingkatan)
                                           ->where('jenis_skk', $jenisSkk) // Filter by jenis_skk
                                           ->get();
        $totalSkkCount = $allRelevantSkkItems->count();
        // $actualOverallStatus is not stored, but can be used for logging or display if needed.
        // $actualOverallStatus = ($totalSkkCount > 0 && count($checkedItems) === $totalSkkCount);

        DB::beginTransaction();
        try {
            // Delete existing assessments for this student, tingkatan, jenis_skk, and pembina
            PenilaianSkk::where('siswa_id', $siswaId)
                        ->where('tingkatan', $tingkatan)
                        ->where('jenis_skk', $jenisSkk) // Add jenis_skk to delete criteria
                        ->where('pembina_id', $pembinaId)
                        ->delete();

            // Insert new records for all relevant Skk items
            foreach ($allRelevantSkkItems as $skkItem) {
                $status = in_array($skkItem->id, $checkedItems);

                PenilaianSkk::create([
                    'siswa_id' => $siswaId,
                    'pembina_id' => $pembinaId,
                    'manajemen_skk_id' => $skkItem->id,
                    'status' => $status,
                    'tanggal' => $assessmentDate,
                    'tingkatan' => $tingkatan,
                    'jenis_skk' => $jenisSkk, // Store jenis_skk
                ]);
            }

            DB::commit();
            return redirect()->route('nilai_skk.index')->with('success', 'Penilaian SKK berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
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
        $tingkatans = ['purwa', 'madya', 'utama'];
        $siswas = Siswa::all();
        $jenisSkks = ManajemenSkk::distinct()->pluck('jenis_skk'); // For the dropdown

        $skkItemsForTingkatanAndJenisSkk = ManajemenSkk::where('tingkatan', $tingkatan)
                                                        ->where('jenis_skk', $jenis_skk)
                                                        ->get();

        $existingAssessments = PenilaianSkk::where('siswa_id', $siswa_id)
                                            ->where('tingkatan', $tingkatan)
                                            ->where('jenis_skk', $jenis_skk) // Filter by jenis_skk
                                            ->where('pembina_id', $pembinaId)
                                            ->whereIn('manajemen_skk_id', $skkItemsForTingkatanAndJenisSkk->pluck('id'))
                                            ->get()
                                            ->keyBy('manajemen_skk_id');

        $penilaianSkk = PenilaianSkk::where('siswa_id', $siswa_id)
                                    ->where('tingkatan', $tingkatan)
                                    ->where('jenis_skk', $jenis_skk) // Filter by jenis_skk
                                    ->where('pembina_id', $pembinaId)
                                    ->orderBy('tanggal', 'desc')
                                    ->first();

        // If no existing assessment found for this combination, initialize a new one for the form
        if (!$penilaianSkk) {
            $penilaianSkk = new PenilaianSkk();
            $penilaianSkk->siswa_id = $siswa_id;
            $penilaianSkk->tingkatan = $tingkatan;
            $penilaianSkk->jenis_skk = $jenis_skk; // Set jenis_skk for the new instance
            $penilaianSkk->tanggal = date('Y-m-d');
        }

        return view('pembina.nilai_skk.edit', compact(
            'penilaianSkk',
            'siswa',
            'siswas',
            'tingkatans',
            'tingkatan',
            'jenis_skk', // Pass jenis_skk to the view
            'jenisSkks', // Pass all unique jenis_skks for the dropdown
            'skkItemsForTingkatanAndJenisSkk',
            'existingAssessments'
        ));
    }

    public function update(Request $request, $siswa_id_route, $tingkatan_route, $jenis_skk_route)
    {
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tingkatan' => 'required|string|in:purwa,madya,utama',
            'jenis_skk' => 'required|string', // Validate jenis_skk
            'assessment_date' => 'required|date',
            'checked_skk_items' => 'array',
            'checked_skk_items.*' => 'exists:manajemen_skks,id',
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
        $jenisSkkSubmitted = $validatedData['jenis_skk']; // Get jenis_skk from validated data
        $assessmentDate = $validatedData['assessment_date'];
        $checkedItems = $validatedData['checked_skk_items'] ?? [];

        $allRelevantSkkItems = ManajemenSkk::where('tingkatan', $tingkatanSubmitted)
                                           ->where('jenis_skk', $jenisSkkSubmitted) // Filter by jenis_skk
                                           ->get();
        $totalSkkCount = $allRelevantSkkItems->count();
        // $actualOverallStatus = ($totalSkkCount > 0 && count($checkedItems) === $totalSkkCount);

        DB::beginTransaction();
        try {
            // Delete all previous assessments for this student, tingkatan, jenis_skk, and pembina
            PenilaianSkk::where('siswa_id', $siswaId)
                        ->where('tingkatan', $tingkatanSubmitted)
                        ->where('jenis_skk', $jenisSkkSubmitted) // Add jenis_skk to delete criteria
                        ->where('pembina_id', $pembinaId)
                        ->delete();

            // Insert new records based on checked status
            foreach ($allRelevantSkkItems as $skkItem) {
                $status = in_array($skkItem->id, $checkedItems);

                PenilaianSkk::create([
                    'siswa_id' => $siswaId,
                    'pembina_id' => $pembinaId,
                    'manajemen_skk_id' => $skkItem->id,
                    'status' => $status,
                    'tanggal' => $assessmentDate,
                    'tingkatan' => $tingkatanSubmitted,
                    'jenis_skk' => $jenisSkkSubmitted, // Store jenis_skk
                ]);
            }

            DB::commit();
            return redirect()->route('nilai_skk.index')->with('success', 'Penilaian SKK berhasil diubah!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating Penilaian SKK: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal mengubah penilaian SKK. Silakan coba lagi.']);
        }
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

        try {
            PenilaianSkk::where('siswa_id', $siswa_id)
                        ->where('tingkatan', $tingkatan)
                        ->where('jenis_skk', $jenis_skk) // Add jenis_skk to delete criteria
                        ->where('pembina_id', $pembinaId)
                        ->delete();

            return redirect()->route('nilai_skk.index')->with('success', 'Semua penilaian SKK untuk siswa ini pada tingkatan ' . ucfirst($tingkatan) . ' dan jenis SKK "' . $jenis_skk . '" berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting Penilaian SKK group: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus penilaian SKK. Silakan coba lagi.');
        }
    }
}