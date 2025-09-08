<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NilaiAkademik;
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\NilaiAkademikImport;
use App\Exports\NilaiAkademikExport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Validators\ValidationException;

class ManajemenNilaiAkademik extends Controller
{
    private $categories = [
        'Matematika',
        'IPA',
        'IPS',
        'Olahraga',
        'Bahasa Indonesia',
        'Bahasa Inggris',
    ];

    public function index(Request $request)
    {
        $guru = Auth::user()->guru;
        $selectedCategory = $request->query('mata_pelajaran');

        // Mapel milik guru (untuk disable option di Blade)
        $guruSubject = $guru->mata_pelajaran;

        // Filter siswa sesuai kelas guru (jika ada)
        $siswasQuery = Siswa::query();
        if (!empty($guru->kelas)) {
            $siswasQuery->where('kelas', $guru->kelas);
        }
        $siswas = $siswasQuery->orderBy('nama')->get();

        // Hanya ambil nilai jika mapel yang dipilih = mapel guru
        $existingScoresMap = collect();
        if ($selectedCategory && $selectedCategory === $guruSubject) {
            $existingScoresMap = NilaiAkademik::where('guru_id', $guru->id)
                ->where('mata_pelajaran', $selectedCategory)
                ->get()
                ->keyBy('siswa_id');
        } else {
            // Jika user memaksa pilih mapel lain lewat URL, kosongkan supaya tombol nonaktif
            $selectedCategory = null;
        }

        return view('guru.nilai_akademik.index', [
            'siswas' => $siswas,
            'guru' => $guru,
            'selectedCategory' => $selectedCategory,
            'existingScoresMap' => $existingScoresMap,
            'categories' => $this->categories,
            'guruSubject' => $guruSubject
        ]);
    }

    public function create(Request $request)
    {
        $guru = Auth::user()->guru;
        $selectedCategory = $request->query('mata_pelajaran');

        // Wajib ada mapel & harus sama dengan mapel guru
        if (empty($selectedCategory) || $selectedCategory !== $guru->mata_pelajaran) {
            return redirect()->route('nilai_akademik.index')
                ->with('error', 'Pilih mata pelajaran Anda terlebih dahulu.');
        }

        // Filter siswa sesuai kelas guru (jika ada)
        $siswasQuery = Siswa::query();
        if (!empty($guru->kelas)) {
            $siswasQuery->where('kelas', $guru->kelas);
        }
        $siswas = $siswasQuery->orderBy('nama')->get();

        // Ambil nilai yang sudah ada
        $existingScores = NilaiAkademik::where('guru_id', $guru->id)
            ->where('mata_pelajaran', $selectedCategory)
            ->get()
            ->keyBy('siswa_id');

        // Tanggal import terakhir
        $lastUpdated = NilaiAkademik::where('guru_id', $guru->id)
            ->where('mata_pelajaran', $selectedCategory)
            ->max('updated_at');

        // Map siswa + nilai
        $siswasWithScores = $siswas->map(function ($siswa) use ($existingScores) {
            $siswaData = $siswa->toArray();
            $existingScore = $existingScores->get($siswa->id);
            $siswaData['nilai'] = $existingScore ? $existingScore->nilai : null;
            return (object) $siswaData;
        });

        return view('guru.nilai_akademik.create', compact(
            'siswasWithScores', 'guru', 'selectedCategory', 'lastUpdated'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mata_pelajaran' => 'required|string|max:255',
            'scores' => 'required|array',
            'scores.*.siswa_id' => 'required|exists:siswas,id',
            'scores.*.nilai' => 'required|numeric|min:0|max:100',
        ]);

        $guru = Auth::user()->guru;

        // Pastikan mapel yang disubmit adalah mapel milik guru
        if ($request->mata_pelajaran !== $guru->mata_pelajaran) {
            return redirect()->route('nilai_akademik.index')
                ->with('error', 'Anda hanya dapat menginput nilai untuk mata pelajaran Anda sendiri.');
        }

        DB::beginTransaction();
        try {
            foreach ($request->scores as $scoreData) {
                $siswaId = $scoreData['siswa_id'];
                $nilaiInput = $scoreData['nilai'];

                // (Opsional keamanan extra) Pastikan siswa berada di kelas guru, jika guru punya kelas
                if (!empty($guru->kelas)) {
                    $allowed = Siswa::where('id', $siswaId)->where('kelas', $guru->kelas)->exists();
                    if (!$allowed) {
                        continue; // skip siswa yang bukan kelasnya
                    }
                }

                NilaiAkademik::updateOrCreate(
                    [
                        'siswa_id' => $siswaId,
                        'guru_id' => $guru->id,
                        'mata_pelajaran' => $request->mata_pelajaran,
                    ],
                    [
                        'nilai' => $nilaiInput,
                    ]
                );
            }

            DB::commit();
            return redirect()->route('nilai_akademik.index', ['mata_pelajaran' => $request->mata_pelajaran])
                ->with('success', 'Nilai akademik berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Terjadi kesalahan saat menyimpan nilai akademik: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan nilai: ' . $e->getMessage());
        }
    }

    public function edit(NilaiAkademik $nilaiAkademik)
    {
        if ($nilaiAkademik->guru_id !== Auth::user()->guru->id) {
            return redirect()->route('nilai_akademik.index')->with('error', 'Anda tidak memiliki akses untuk mengedit nilai ini.');
        }

        $guru = Auth::user()->guru;
        $siswa = $nilaiAkademik->siswa;
        $selectedCategory = $nilaiAkademik->mata_pelajaran;

        return view('guru.nilai_akademik.edit', compact('nilaiAkademik', 'siswa', 'guru', 'selectedCategory'));
    }

    public function update(Request $request, NilaiAkademik $nilaiAkademik)
    {
        if ($nilaiAkademik->guru_id !== Auth::user()->guru->id) {
            return redirect()->route('nilai_akademik.index')->with('error', 'Anda tidak memiliki akses untuk memperbarui nilai ini.');
        }

        $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
        ]);

        $nilaiAkademik->update([
            'nilai' => $request->nilai,
        ]);

        return redirect()->route('nilai_akademik.index', ['mata_pelajaran' => $nilaiAkademik->mata_pelajaran])->with('success', 'Nilai akademik berhasil diperbarui!');
    }

    public function destroy(NilaiAkademik $nilaiAkademik)
    {
        if ($nilaiAkademik->guru_id !== Auth::user()->guru->id) {
            return redirect()->route('nilai_akademik.index')->with('error', 'Anda tidak memiliki akses untuk menghapus nilai ini.');
        }

        $nilaiAkademik->delete();

        $selectedCategory = request()->query('mata_pelajaran');
        return redirect()->route('nilai_akademik.index', ['mata_pelajaran' => $selectedCategory])->with('success', 'Nilai akademik berhasil dihapus!');
    }

    public function showImportForm(Request $request)
    {
        $selectedCategory = $request->query('mata_pelajaran');

        if (empty($selectedCategory)) {
            return redirect()->route('nilai_akademik.index')->with('error', 'Pilih mata pelajaran terlebih dahulu untuk mengimpor nilai.');
        }

        // Pastikan yang diimpor adalah mapel milik guru
        $guru = Auth::user()->guru;
        if ($selectedCategory !== $guru->mata_pelajaran) {
            return redirect()->route('nilai_akademik.index')->with('error', 'Anda hanya dapat mengimpor nilai untuk mata pelajaran Anda.');
        }

        return view('guru.nilai_akademik.import', compact('selectedCategory'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'mata_pelajaran_impor' => 'required|string|max:255',
        ]);

        $guru = Auth::user()->guru;
        if ($request->mata_pelajaran_impor !== $guru->mata_pelajaran) {
            return redirect()->route('nilai_akademik.index')->with('error', 'Anda hanya dapat mengimpor nilai untuk mata pelajaran Anda.');
        }

        try {
            $mataPelajaranImpor = $request->mata_pelajaran_impor;

            $import = new NilaiAkademikImport($mataPelajaranImpor);
            Excel::import($import, $request->file('file'));

            return redirect()->route('nilai_akademik.index', ['mata_pelajaran' => $mataPelajaranImpor])
                ->with('success', 'Data nilai akademik berhasil diimpor dan disimpan!');

        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = 'Baris ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            return redirect()->back()->with('error', 'Gagal mengimpor file karena masalah validasi: <br><ul><li>' . implode('</li><li>', $errors) . '</li></ul>');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengimpor file: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor file: ' . $e->getMessage() . '. Silakan cek log untuk detail.');
        }
    }

    public function lihatNilaiAkademik()
    {
        $siswas = Siswa::with(['nilai_akademik' => function($query) {
            $query->whereIn('mata_pelajaran', $this->categories);
        }])->get();

        $data = $siswas->map(function ($siswa, $index) {
            $row = [
                'no' => $index + 1,
                'nisn' => $siswa->nisn,
                'nama_siswa' => $siswa->nama,
            ];

            $scoresBySubject = $siswa->nilai_akademik->keyBy('mata_pelajaran');

            foreach ($this->categories as $category) {
                $score = $scoresBySubject->get($category);
                $columnName = strtolower(str_replace(' ', '_', $category));
                $row[$columnName] = $score ? $score->nilai : '-';
            }
            return $row;
        });

        return view('pembina.lihat_nilai.nilai_akademik', [
            'data' => $data,
            'categories' => $this->categories,
        ]);
    }

    public function exportNilaiAkademik()
    {
        $fileName = 'nilai_akademik_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new NilaiAkademikExport(), $fileName);
    }
}
