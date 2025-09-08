<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Lomba;
use Illuminate\Support\Facades\Log;
use App\Models\HasilClustering;
use App\Exports\HasilClusteringExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Siswa;
use App\Models\ClusteringFinal;
use Illuminate\Support\Facades\DB;
use App\Exports\ClusteringFinalExport;

class RecommendationController extends Controller
{
    protected $baseUrl;
    protected $flaskApiUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('FLASK_API_URL', 'http://flask:5000'), '/');
        $this->flaskApiUrl = $this->baseUrl . '/api/recommendations';
    }

    public function index()
    {
        $error = null;
        $message = null;
        $rekomendasi = [];
        $clusterMapping = [];
        $lombaStatus = [];
        $lombaRankings = [];
        $metrics = [];

        try {
            $check = Http::timeout(5)->get($this->baseUrl . '/');
            if (!$check->successful()) {
                $error = 'Flask API tidak merespons.';
            } else {
                $rekomendasi = Http::get($this->baseUrl . '/api/recommendations')->json();
                $clusterMapping = Http::get($this->baseUrl . '/api/cluster-mapping')->json();
                $lombaStatus = Http::get($this->baseUrl . '/api/lomba-status')->json();
                $lombaRankings = Http::get($this->baseUrl . '/api/lomba-rankings')->json();
                $metrics = Http::get($this->baseUrl . '/api/clustering-metrics')->json();
            }
        } catch (\Exception $e) {
            $error = 'Kesalahan saat mengakses Flask API: ' . $e->getMessage();
        }

        $allLombas = [];
        $lombas = Lomba::with('variabel')->where('status', 1)->get();
        foreach ($lombas as $lomba) {
            if ($lomba->variabel) {
                $jenis = $lomba->variabel->jenis_lomba;
                $slug = strtolower(str_replace([' ', '_'], '-', $jenis));
                $allLombas[$slug] = $jenis;
            }
        }

        return view('pembina.rekomendasi.index', [
            'rekomendasi' => $rekomendasi,
            'clusterMapping' => $clusterMapping,
            'lombaStatus' => $lombaStatus,
            'lombaRankings' => $lombaRankings,
            'silhouette_score' => $metrics['silhouette_score'] ?? null,
            'db_index' => $metrics['davies_bouldin_index'] ?? null,
            'error' => $error,
            'message' => $message,
            'allLombas' => $allLombas
        ]);
    }

    public function dataNormalisasi()
    {
        $response = Http::get($this->baseUrl . '/api/normalized-data');

        if ($response->ok()) {
            $normalizedData = $response->json();
            return view('pembina.rekomendasi.normalisasi', compact('normalizedData'));
        }

        return back()->with('error', 'Gagal mengambil data normalisasi. ' . $response->body());
    }

    public function status()
    {
        $lombas = Lomba::with('variabel')->get();
        $clusteringData = HasilClustering::all();

        $result = $lombas->map(function ($lomba) use ($clusteringData) {
            $jenisLomba = $lomba->variabel->jenis_lomba ?? '-';
            $terisi = $clusteringData->where('kategori_lomba', $jenisLomba)->count();
            $status = $terisi >= $lomba->jumlah_siswa ? 'Terpenuhi' : 'Belum Terpenuhi';

            return [
                'lomba' => $jenisLomba,
                'kebutuhan' => $lomba->jumlah_siswa,
                'terisi' => $terisi,
                'status' => $status,
            ];
        });

        return view('pembina.rekomendasi.status', [
            'statusData' => $result
        ]);
    }

    public function ranking()
    {
        try {
            $response = Http::get($this->baseUrl . '/api/lomba-rankings');
            if ($response->successful()) {
                $rankingData = $response->json();
                return view('pembina.rekomendasi.ranking', ['rankingData' => $rankingData]);
            }
            return redirect()->back()->with('error', 'Gagal memuat data ranking lomba.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal terhubung ke Flask API.');
        }
    }

    protected function getCompetitionsData()
    {
        $data = [];

        $lombas = Lomba::with('variabel')->where('status', 1)->get();

        foreach ($lombas as $lomba) {
            $clustering = $lomba->variabel;

            if ($clustering) {
                $variabelAkademik = $clustering->variabel_akademiks ?? [];
                $variabelNonAkademik = $clustering->variabel_non_akademiks ?? [];

                $data[] = [
                    'Lomba' => $clustering->jenis_lomba,
                    'Jumlah Siswa yang Dibutuhkan' => $lomba->jumlah_siswa,
                    'Variabel yang Digunakan' => array_merge($variabelAkademik, $variabelNonAkademik)
                ];
            }
        }

        return $data;
    }

    public function showByLomba($lombaSlug)
    {
        $allLombas = [];
        $lombas = Lomba::with('variabel')->where('status', 1)->get();
        foreach ($lombas as $lomba) {
            if ($lomba->variabel) {
                $jenis = $lomba->variabel->jenis_lomba;
                $slug = strtolower(str_replace([' ', '_'], '-', $jenis));
                $allLombas[$slug] = $jenis;
            }
        }

        $lombaName = $allLombas[$lombaSlug] ?? null;

        if (!$lombaName) {
            return view('pembina.rekomendasi.lomba_detail', [
                'error' => 'Lomba tidak ditemukan.',
                'lombaName' => null,
                'rekomendasi' => [],
                'requiredNum' => 0,
                'message' => null,
            ]);
        }

        $rekomendasi = [];
        $error = null;
        $message = null;

        try {
            $statusCheck = Http::timeout(5)->get($this->baseUrl . '/');
            if (!$statusCheck->successful()) {
                $error = 'Flask API tidak merespons. Pastikan API berjalan.';
            } else {
                $allData = Http::timeout(60)->get($this->flaskApiUrl)->json();

                if (is_array($allData)) {
                    $rekomendasi = collect($allData)->filter(function ($item) use ($lombaName) {
                        return isset($item['Lomba Rekomendasi']) && $item['Lomba Rekomendasi'] === $lombaName;
                    })->values()->all();

                    if (empty($rekomendasi)) {
                        $message = 'Tidak ada rekomendasi untuk lomba ini.';
                    }
                } else {
                    $error = 'Format data dari Flask tidak sesuai.';
                }
            }
        } catch (\Exception $e) {
            $error = 'Terjadi kesalahan saat mengakses Flask API: ' . $e->getMessage();
        }

        $requiredNum = $lombas->firstWhere(fn ($l) => $l->variabel && $l->variabel->jenis_lomba === $lombaName)?->jumlah_siswa ?? 0;

        return view('pembina.rekomendasi.lomba_detail', compact('rekomendasi', 'lombaName', 'requiredNum', 'error', 'message'));
    }

    public function detailPotensi()
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl . '/api/versatile-students');

            if ($response->successful()) {
                $versatileData = $response->json();
                return view('pembina.rekomendasi.detail_potensi', compact('versatileData'));
            }

            return back()->with('error', 'Gagal mengambil data siswa serbaguna. ' . $response->body());
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengakses Flask API: ' . $e->getMessage());
        }
    }

    public function save(Request $request, $lombaSlug)
    {
        $lombas = Lomba::with('variabel')->where('status', 1)->get();
        $lombaName = null;

        foreach ($lombas as $lomba) {
            if ($lomba->variabel) {
                $slug = strtolower(str_replace([' ', '_'], '-', $lomba->variabel->jenis_lomba));
                if ($slug === $lombaSlug) {
                    $lombaName = $lomba->variabel->jenis_lomba;
                    break;
                }
            }
        }

        if (!$lombaName) {
            return redirect()->back()->with('error', 'Lomba tidak ditemukan.');
        }

        $rekomendasi = $request->input('rekomendasi', []);

        foreach ($rekomendasi as $rec) {
            $decoded = json_decode($rec, true);
            if (!$decoded || !isset($decoded['Nama Siswa'], $decoded['Rata-rata Skor Lomba'])) {
                continue;
            }

            $siswa = Siswa::where('nama', $decoded['Nama Siswa'])->first();
            if (!$siswa) continue;

            HasilClustering::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'kategori_lomba' => $lombaName,
                ],
                [
                    'rata_rata_skor' => $decoded['Rata-rata Skor Lomba'],
                ]
            );
        }

        return redirect()->back()->with('message', 'Data rekomendasi berhasil disimpan.');
    }

    protected function fetchClusteringMetrics()
    {
        try {
            $response = Http::timeout(10)->get('http://127.0.0.1:5000/api/clustering-metrics');
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Gagal fetch metrics: ' . $e->getMessage());
        }
        return ['silhouette_score' => null, 'davies_bouldin_index' => null];
    }

    public function grafik()
    {
        $data = HasilClustering::all();

        $grouped = $data->groupBy('kategori_lomba')->map(function ($group) {
            return $group->count();
        });

        $clusters = [];
        $clusterIndex = 1;
        foreach ($grouped->keys() as $kategori) {
            $clusters[$kategori] = $clusterIndex++;
        }

        $metrics = $this->fetchClusteringMetrics();

        return view('pembina.rekomendasi.grafik_rekomendasi', [
            'labels' => $grouped->keys(),
            'counts' => $grouped->values(),
            'tableData' => $grouped,
            'clusters' => $clusters,
            'silhouette_score' => $metrics['silhouette_score'] ?? null,
            'db_index' => $metrics['davies_bouldin_index'] ?? null,
        ]);
    }

    public function export()
    {
        return Excel::download(new HasilClusteringExport, 'hasil_rekomendasi.xlsx');
    }

    public function finalClustering(Request $request)
    {
        try {
            $query = HasilClustering::with('siswa');

            // Filter berdasarkan gender (1 = laki-laki, 0 = perempuan)
            if ($request->has('gender') && in_array($request->input('gender'), ['1', '0'], true)) {
                $gender = (int) $request->input('gender');
                $query->whereHas('siswa', function ($q) use ($gender) {
                    $q->where('jenis_kelamin', $gender);
                });
            }

            $hasilClusterings = $query->get();

            $selectedData = ClusteringFinal::pluck('siswa_id')->toArray();

            return view('pembina.rekomendasi.final_clustering', [
                'hasilClusterings' => $hasilClusterings,
                'selectedData' => $selectedData,
                'selectedGender'   => $request->input('gender'),
            ]);

        } catch (\Exception $e) {
            Log::error("Error di finalClustering: " . $e->getMessage());
            return view('pembina.rekomendasi.final_clustering', [
                'hasilClusterings' => collect([]),
                'error'            => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage(),
                'selectedGender'   => null,
            ]);
        }
    }

    public function saveFinalClustering(Request $request)
    {

    //    $selected = $request->input('selected', []);

        $selected = HasilClustering::whereIn('siswa_id', $request->input('selected', []))
                            ->pluck('siswa_id')
                            ->toArray();

        ClusteringFinal::whereNotIn('siswa_id', $selected)->delete();

        $hasilClusterings = HasilClustering::whereIn('siswa_id', $selected)->get()->keyBy('siswa_id');

        foreach($selected as $siswa_id) {
            $data = $hasilClusterings[$siswa_id];
            ClusteringFinal::updateOrCreate(
                ['siswa_id' => $siswa_id],
                [
                    'rata_rata_skor' => $data->rata_rata_skor,
                    'kategori_lomba' => $data->kategori_lomba
                ]
            );
        }


          return back()->with('success', 'Data clustering final berhasil disinkronkan.');

    }

        // try {
        //     DB::transaction(function () use ($selectedIds, $gender) {
        //         // Hapus data lama berdasarkan gender (jika ada filter)
        //         $existingQuery = ClusteringFinal::query();

        //         if ($gender) {
        //             $existingQuery->whereHas('siswa', function ($q) use ($gender) {
        //                 $q->where('jenis_kelamin', $gender);
        //             });
        //         }

        //         $existingQuery->delete();

        //         // Ambil siswa yang dipilih
        //         $siswas = Siswa::whereIn('id', $selectedIds)->get();

        //         // Map data untuk upsert
        //         $dataToInsert = $siswas->map(function ($siswa) {
        //             return [
        //                 'siswa_id'       => $siswa->id,
        //                 'kategori_lomba' => $siswa->kategori_lomba ?? 'Pioneering',
        //                 'rata_rata_skor' => $siswa->rata_rata_skor ?? 0,
        //                 'created_at'     => now(),
        //                 'updated_at'     => now(),
        //             ];
        //         })->toArray();

        //         // Simpan / update data
        //         ClusteringFinal::upsert(
        //             $dataToInsert,
        //             ['siswa_id', 'kategori_lomba'], // unique key
        //             ['rata_rata_skor', 'updated_at'] // kolom yang diupdate
        //         );
        //     });

        //     return back()->with('success', 'Data clustering final berhasil disinkronkan.');
        // } catch (\Exception $e) {
        //     return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        // }
    // }

    public function exportFinalClustering(Request $request)
    {
        try {
            $gender = $request->input('gender');

            $fileName = 'clustering_final_data';
            if ($gender !== null && $gender !== '') {
                $fileName .= '_gender_' . $gender;
            }
            $fileName .= '_' . now()->format('Ymd_His') . '.xlsx';

            return Excel::download(new ClusteringFinalExport($gender), $fileName);
        } catch (\Exception $e) {
            Log::error("Error exporting final clustering: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengekspor data: ' . $e->getMessage());
        }
    }
}
