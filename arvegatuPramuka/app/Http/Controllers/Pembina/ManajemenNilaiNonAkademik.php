<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NilaiNonAkademik;
use App\Models\Siswa;
use App\Models\Pembina;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\NilaiNonAkademikImport;
use App\Exports\NilaiNonAkademikExport;
use Illuminate\Support\Facades\Log; // Untuk debugging
use Maatwebsite\Excel\Validators\ValidationException;

class ManajemenNilaiNonAkademik extends Controller
{
    private $categories = [
        'Nilai Tes Bahasa',
        'Nilai TIK',
        'Kehadiran',
        'Skor Penerapan',
        'Nilai Hasta Karya',
    ];

    public function index(Request $request)
    {
        $pembina = Auth::user()->pembina;
        $selectedCategory = $request->query('kategori');

        // Ambil kategori & kelas pembina
        $pembinaCategory = $pembina->kategori;
        $pembinaClass = $pembina->kelas; // Pastikan field 'kelas' ada di tabel pembinas

        // Ambil siswa sesuai kelas pembina jika ada
        $siswas = Siswa::query();
        if (!empty($pembinaClass)) {
            $siswas->where('kelas', $pembinaClass);
        }
        $siswas = $siswas->get();

        // Ambil nilai yang sudah ada
        $existingScoresMap = collect();
        if ($selectedCategory) {
            $existingScoresMap = NilaiNonAkademik::where('pembina_id', $pembina->id)
                ->where('kategori', $selectedCategory)
                ->get()
                ->keyBy('siswa_id');
        }

        return view('pembina.nilai_non_akademik.index', [
            'siswas' => $siswas,
            'pembina' => $pembina,
            'selectedCategory' => $selectedCategory,
            'existingScoresMap' => $existingScoresMap,
            'categories' => $this->categories, // daftar kategori lomba / kegiatan
            'pembinaCategory' => $pembinaCategory
        ]);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $pembina = $user->pembina;

        // Ambil kategori dari request atau session
        $selectedCategory = $request->kategori;

        // Jika pembina kategori "Pembina Pramuka", maka hanya bisa input untuk kategori itu
        if ($pembina->kategori === 'Pembina Pramuka') {
            $selectedCategory = 'Pembina Pramuka';
        }

        // Filter siswa sesuai kelas pembina
        $query = Siswa::query();
        if (!empty($pembina->kelas)) {
            $query->where('kelas', $pembina->kelas);
        }

        // Ambil siswa dengan nilai terakhir (jika ada)
        $siswasWithScores = $query
            ->leftJoin('nilai_non_akademiks', function ($join) use ($selectedCategory) {
                $join->on('siswas.id', '=', 'nilai_non_akademiks.siswa_id')
                    ->where('nilai_non_akademiks.kategori', '=', $selectedCategory);
            })
            ->select('siswas.*', 'nilai_non_akademiks.nilai')
            ->orderBy('siswas.nama', 'asc')
            ->get();

        // Cek kapan terakhir import untuk kategori ini
        $lastUpdated = DB::table('nilai_non_akademiks')
            ->where('kategori', $selectedCategory)
            ->max('updated_at');

        return view('pembina.nilai_non_akademik.create', compact(
            'siswasWithScores',
            'selectedCategory',
            'lastUpdated'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori' => 'required|string|max:255',
            'scores' => 'required|array',
            'scores.*.siswa_id' => 'required|exists:siswas,id',
            'scores.*.nilai' => 'required|numeric|min:0|max:100', // Validasi untuk satu input 'nilai'
        ]);

        $pembinaId = Auth::user()->pembina->id;
        $kategori = $request->kategori;

        DB::beginTransaction();
        try {
            foreach ($request->scores as $scoreData) {
                $siswaId = $scoreData['siswa_id'];
                $nilaiInput = $scoreData['nilai']; // Ambil nilai langsung

                NilaiNonAkademik::updateOrCreate(
                    [
                        'siswa_id' => $siswaId,
                        'pembina_id' => $pembinaId,
                        'kategori' => $kategori,
                    ],
                    [
                        'nilai' => $nilaiInput, // Simpan nilai langsung
                    ]
                );
            }

            DB::commit();
            return redirect()->route('nilai_non_akademik.index', ['kategori' => $kategori])->with('success', 'Nilai non akademik berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Terjadi kesalahan saat menyimpan nilai non akademik: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan nilai: ' . $e->getMessage());
        }
    }

    public function edit(NilaiNonAkademik $nilaiNonAkademik)
    {
        if ($nilaiNonAkademik->pembina_id !== Auth::user()->pembina->id) {
            return redirect()->route('nilai_non_akademik.index')->with('error', 'Anda tidak memiliki akses untuk mengedit nilai ini.');
        }

        $pembina = Auth::user()->pembina;
        $siswa = $nilaiNonAkademik->siswa;
        $selectedCategory = $nilaiNonAkademik->kategori;

        return view('pembina.nilai_non_akademik.edit', compact('nilaiNonAkademik', 'siswa', 'pembina', 'selectedCategory'));
    }

    public function update(Request $request, NilaiNonAkademik $nilaiNonAkademik)
    {
        if ($nilaiNonAkademik->pembina_id !== Auth::user()->pembina->id) {
            return redirect()->route('nilai_non_akademik.index')->with('error', 'Anda tidak memiliki akses untuk memperbarui nilai ini.');
        }

        $request->validate([
            'nilai' => 'required|numeric|min:0|max:100', // Validasi untuk satu input 'nilai'
        ]);

        $nilaiNonAkademik->update([
            'nilai' => $request->nilai, // Simpan nilai langsung
        ]);

        return redirect()->route('nilai_non_akademik.index', ['kategori' => $nilaiNonAkademik->kategori])->with('success', 'Nilai non akademik berhasil diperbarui!');
    }

    public function destroy(NilaiNonAkademik $nilaiNonAkademik)
    {
        if ($nilaiNonAkademik->pembina_id !== Auth::user()->pembina->id) {
            return redirect()->route('nilai_non_akademik.index')->with('error', 'Anda tidak memiliki akses untuk menghapus nilai ini.');
        }

        $nilaiNonAkademik->delete();

        $selectedCategory = request()->query('kategori');
        return redirect()->route('nilai_non_akademik.index', ['kategori' => $selectedCategory])->with('success', 'Nilai non akademik berhasil dihapus!');
    }

    public function showImportForm(Request $request)
    {
        $selectedCategory = $request->query('kategori');

        if (empty($selectedCategory)) {
            return redirect()->route('nilai_non_akademik.index')->with('error', 'Pilih kategori terlebih dahulu untuk mengimpor nilai.');
        }

        return view('pembina.nilai_non_akademik.import', compact('selectedCategory'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'kategori_impor' => 'required|string|max:255',
        ]);

        try {
            $kategoriImpor = $request->kategori_impor;

            $import = new NilaiNonAkademikImport($kategoriImpor);
            Excel::import($import, $request->file('file'));

            return redirect()->route('nilai_non_akademik.index', ['kategori' => $kategoriImpor])
                             ->with('success', 'Data nilai non akademik berhasil diimpor dan disimpan!');

        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = 'Baris ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            return redirect()->back()->with('error', 'Gagal mengimpor file karena masalah validasi: ' . implode('; ', $errors));
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengimpor file: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor file: ' . $e->getMessage() . '. Silakan cek log untuk detail.');
        }
    }

    public function lihatNilaiNonAkademik()
    {
        // Mengambil semua siswa beserta nilai non-akademik mereka.
        // Eager loading 'nilaiNonAkademiks' untuk menghindari N+1 query problem.
        // Memfilter nilai non-akademik hanya untuk kategori yang relevan.
        $siswas = Siswa::with(['nilai_non_akademik' => function($query) {
            $query->whereIn('kategori', $this->categories);
        }])->get();

        // Memproses data siswa untuk ditampilkan di datatable.
        // Setiap baris akan berisi No, NISN, Nama Siswa, dan nilai untuk setiap kategori non-akademik.
        $data = $siswas->map(function ($siswa, $index) {
            $row = [
                'no' => $index + 1, // Nomor urut
                'nisn' => $siswa->nisn, // Asumsi model Siswa memiliki kolom 'nisn'
                'nama_siswa' => $siswa->nama, // Asumsi model Siswa memiliki kolom 'nama'
            ];

            // Mengubah koleksi nilai non-akademik siswa menjadi associative array
            // dengan 'kategori' sebagai kunci untuk akses mudah.
            $scoresByCategory = $siswa->nilai_non_akademik->keyBy('kategori');

            // Menambahkan nilai untuk setiap kategori non-akademik.
            // Jika tidak ada nilai untuk kategori tertentu, tampilkan '-'.
            foreach ($this->categories as $category) {
                $score = $scoresByCategory->get($category);
                // Menggunakan nama kolom yang sesuai dengan format DataTables
                $columnName = strtolower(str_replace(' ', '_', $category));
                $row[$columnName] = $score ? $score->nilai : '-';
            }
            return $row;
        });

        // Mengembalikan view dengan data yang sudah diproses dan daftar kategori non-akademik.
        return view('pembina.lihat_nilai.nilai_non_akademik', [
            'data' => $data,
            'categories' => $this->categories,
        ]);
    }

    public function exportNilaiNonAkademik()
    {
        $fileName = 'nilai_non_akademik_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new NilaiNonAkademikExport(), $fileName);
    }
}