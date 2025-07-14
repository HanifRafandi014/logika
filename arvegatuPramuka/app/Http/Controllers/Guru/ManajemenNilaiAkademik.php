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
    // Daftar kategori mata pelajaran yang tersedia
    private $categories = [
        'Matematika',
        'IPA',
        'IPS',
        'Olahraga',
        'Bahasa Indonesia',
        'Bahasa Inggris',
    ];

    /**
     * Menampilkan daftar siswa dan nilai akademik yang sudah ada untuk mata pelajaran tertentu.
     */
    public function index(Request $request)
    {
        // Mendapatkan objek guru yang terkait dengan user yang sedang login
        $guru = Auth::user()->guru;
        // Mendapatkan mata pelajaran yang dipilih dari query parameter
        $selectedCategory = $request->query('mata_pelajaran');

        // Mendapatkan semua data siswa
        $siswas = Siswa::all();

        // Mengambil nilai yang sudah ada untuk guru dan mata pelajaran yang dipilih
        $existingScoresMap = collect();
        if ($selectedCategory) {
            $existingScoresMap = NilaiAkademik::where('guru_id', $guru->id)
                                             ->where('mata_pelajaran', $selectedCategory)
                                             ->get()
                                             ->keyBy('siswa_id');
        }

        // Mengirim data ke view
        return view('guru.nilai_akademik.index', compact('siswas', 'guru', 'selectedCategory', 'existingScoresMap'))->with('categories', $this->categories);
    }

    /**
     * Menampilkan form untuk memasukkan nilai akademik baru atau mengedit yang sudah ada.
     */
    public function create(Request $request)
    {
        // Mendapatkan objek guru yang terkait dengan user yang sedang login
        $guru = Auth::user()->guru;
        // Mendapatkan semua data siswa
        $siswas = Siswa::all();
        // Mendapatkan mata pelajaran yang dipilih dari query parameter
        $selectedCategory = $request->query('mata_pelajaran');

        // Jika mata pelajaran belum dipilih, redirect kembali dengan pesan error
        if (empty($selectedCategory)) {
            return redirect()->route('nilai_akademik.index')->with('error', 'Pilih mata pelajaran terlebih dahulu.');
        }

        // Ambil nilai yang sudah ada di database untuk kategori dan guru ini
        $existingScores = NilaiAkademik::where('guru_id', $guru->id)
                                       ->where('mata_pelajaran', $selectedCategory)
                                       ->get()
                                       ->keyBy('siswa_id');

        // Cari tanggal import terakhir dari updated_at
        $lastUpdated = NilaiAkademik::where('guru_id', $guru->id)
                                    ->where('mata_pelajaran', $selectedCategory)
                                    ->max('updated_at');

        // Map siswa dengan nilai yang sudah ada
        $siswasWithScores = $siswas->map(function($siswa) use ($existingScores) {
            $siswaData = $siswa->toArray();
            $existingScore = $existingScores->get($siswa->id);
            if ($existingScore) {
                $siswaData['nilai'] = $existingScore->nilai; // Ambil nilai tunggal
            } else {
                $siswaData['nilai'] = null;
            }
            return (object) $siswaData;
        });

        // Kirim juga variabel lastUpdated ke view
        return view('guru.nilai_akademik.create', compact(
            'siswasWithScores', 'guru', 'selectedCategory', 'lastUpdated'
        ));
    }

    /**
     * Menyimpan nilai akademik yang dikirimkan dari form.
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'mata_pelajaran' => 'required|string|max:255', // Validasi ini mengharapkan string (nama mata pelajaran)
            'scores' => 'required|array',
            'scores.*.siswa_id' => 'required|exists:siswas,id',
            'scores.*.nilai' => 'required|numeric|min:0|max:100',
        ]);

        $guruId = Auth::user()->guru->id;
        $mata_pelajaran = $request->mata_pelajaran; // Nilai ini akan disimpan. Pastikan dari frontend ini adalah string nama mata pelajaran, bukan indeks.

        DB::beginTransaction(); // Memulai transaksi database
        try {
            // Iterasi setiap data nilai yang dikirimkan
            foreach ($request->scores as $scoreData) {
                $siswaId = $scoreData['siswa_id'];
                $nilaiInput = $scoreData['nilai'];

                // Mencari atau membuat record NilaiAkademik
                NilaiAkademik::updateOrCreate(
                    [
                        'siswa_id' => $siswaId,
                        'guru_id' => $guruId,
                        'mata_pelajaran' => $mata_pelajaran, // Menyimpan nilai mata pelajaran yang diterima
                    ],
                    [
                        'nilai' => $nilaiInput,
                    ]
                );
            }

            DB::commit(); // Menyelesaikan transaksi jika berhasil
            return redirect()->route('nilai_akademik.index', ['mata_pelajaran' => $mata_pelajaran])->with('success', 'Nilai akademik berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack(); // Mengembalikan transaksi jika terjadi error
            Log::error('Terjadi kesalahan saat menyimpan nilai akademik: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan nilai: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan form untuk mengedit nilai akademik tunggal.
     */
    public function edit(NilaiAkademik $nilaiAkademik)
    {
        // Memastikan guru yang login memiliki akses ke nilai ini
        if ($nilaiAkademik->guru_id !== Auth::user()->guru->id) {
            return redirect()->route('nilai_akademik.index')->with('error', 'Anda tidak memiliki akses untuk mengedit nilai ini.');
        }

        $guru = Auth::user()->guru;
        $siswa = $nilaiAkademik->siswa;
        $selectedCategory = $nilaiAkademik->mata_pelajaran;

        return view('guru.nilai_akademik.edit', compact('nilaiAkademik', 'siswa', 'guru', 'selectedCategory'));
    }

    /**
     * Memperbarui nilai akademik tunggal.
     */
    public function update(Request $request, NilaiAkademik $nilaiAkademik)
    {
        // Memastikan guru yang login memiliki akses untuk memperbarui nilai ini
        if ($nilaiAkademik->guru_id !== Auth::user()->guru->id) {
            return redirect()->route('nilai_akademik.index')->with('error', 'Anda tidak memiliki akses untuk memperbarui nilai ini.');
        }

        // Validasi input nilai
        $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
        ]);

        // Memperbarui nilai
        $nilaiAkademik->update([
            'nilai' => $request->nilai,
        ]);

        return redirect()->route('nilai_akademik.index', ['mata_pelajaran' => $nilaiAkademik->mata_pelajaran])->with('success', 'Nilai akademik berhasil diperbarui!');
    }

    /**
     * Menghapus nilai akademik.
     */
    public function destroy(NilaiAkademik $nilaiAkademik)
    {
        // Memastikan guru yang login memiliki akses untuk menghapus nilai ini
        if ($nilaiAkademik->guru_id !== Auth::user()->guru->id) {
            return redirect()->route('nilai_akademik.index')->with('error', 'Anda tidak memiliki akses untuk menghapus nilai ini.');
        }

        $nilaiAkademik->delete();

        $selectedCategory = request()->query('mata_pelajaran');
        return redirect()->route('nilai_akademik.index', ['mata_pelajaran' => $selectedCategory])->with('success', 'Nilai akademik berhasil dihapus!');
    }

    /**
     * Menampilkan form untuk import nilai akademik dari file Excel.
     */
    public function showImportForm(Request $request)
    {
        $selectedCategory = $request->query('mata_pelajaran');

        // Jika mata pelajaran belum dipilih, redirect kembali dengan pesan error
        if (empty($selectedCategory)) {
            return redirect()->route('nilai_akademik.index')->with('error', 'Pilih mata pelajaran terlebih dahulu untuk mengimpor nilai.');
        }

        return view('guru.nilai_akademik.import', compact('selectedCategory'));
    }

    /**
     * Melakukan import nilai akademik dari file Excel.
     */
    public function import(Request $request)
    {
        // Validasi file dan mata pelajaran untuk import
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'mata_pelajaran_impor' => 'required|string|max:255', // Pastikan ini juga mengirimkan string nama mata pelajaran
        ]);

        try {
            $mataPelajaranImpor = $request->mata_pelajaran_impor; // Nilai ini akan diteruskan ke import class

            // Membuat instance NilaiAkademikImport dan menjalankan import
            $import = new NilaiAkademikImport($mataPelajaranImpor);
            Excel::import($import, $request->file('file'));

            return redirect()->route('nilai_akademik.index', ['mata_pelajaran' => $mataPelajaranImpor])
                             ->with('success', 'Data nilai akademik berhasil diimpor dan disimpan!');

        } catch (ValidationException $e) { // Menangkap exception validasi dari Maatwebsite/Excel
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = 'Baris ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            return redirect()->back()->with('error', 'Gagal mengimpor file karena masalah validasi: <br><ul><li>' . implode('</li><li>', $errors) . '</li></ul>');
        } catch (\Exception $e) { // Menangkap exception umum lainnya
            Log::error('Terjadi kesalahan saat mengimpor file: ' . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor file: ' . $e->getMessage() . '. Silakan cek log untuk detail.');
        }
    }

    /**
     * Menampilkan nilai akademik untuk semua siswa (digunakan oleh pembina/admin).
     */
    public function lihatNilaiAkademik()
    {
        // Mengambil semua siswa dengan eager loading nilai_akademik yang sesuai dengan kategori
        $siswas = Siswa::with(['nilai_akademik' => function($query) {
            $query->whereIn('mata_pelajaran', $this->categories);
        }])->get();

        // Memproses data siswa untuk ditampilkan dalam format tabel
        $data = $siswas->map(function ($siswa, $index) {
            $row = [
                'no' => $index + 1, // Nomor urut
                'nisn' => $siswa->nisn, // Asumsi model Siswa memiliki kolom 'nisn'
                'nama_siswa' => $siswa->nama, // Asumsi model Siswa memiliki kolom 'nama'
            ];

            $scoresBySubject = $siswa->nilai_akademik->keyBy('mata_pelajaran');

            // Mengisi nilai untuk setiap mata pelajaran
            foreach ($this->categories as $category) {
                $score = $scoresBySubject->get($category);
                // Menggunakan nama kolom yang sesuai dengan format DataTables (misal: matematika, ipa)
                $columnName = strtolower(str_replace(' ', '_', $category));
                $row[$columnName] = $score ? $score->nilai : '-';
            }
            return $row;
        });

        // Mengembalikan view dengan data yang sudah diproses dan daftar kategori.
        return view('pembina.lihat_nilai.nilai_akademik', [
            'data' => $data,
            'categories' => $this->categories,
        ]);
    }

    /**
     * Mengekspor nilai akademik ke file Excel.
     */
    public function exportNilaiAkademik()
    {
        $fileName = 'nilai_akademik_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new NilaiAkademikExport(), $fileName);
    }
}
