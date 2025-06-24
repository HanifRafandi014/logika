<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommendationController extends Controller
{
    protected $flaskApiUrl = 'http://127.0.0.1:5000/api/recommendations';

    protected $allLombas = [
        'pionering' => 'Pionering',
        'administrasi-regu' => 'Administrasi_Regu',
        'packing-pengembaraan' => 'Packing_Pengembaraan',
        'semboyan-dan-isyarat' => 'Semboyan_dan_Isyarat',
        'sketsa-panorama' => 'Sketsa_Panorama',
        'peta-pita-dan-peta-perjalanan' => 'Peta_Pita_dan_Peta_Perjalanan',
        'menaksir' => 'Menaksir',
        'pertolongan-pertama' => 'Pertolongan_Pertama',
        'masak-rimba' => 'Masak_Rimba',
        'bivak' => 'Bivak',
        'obat-dan-ramuan-tradisional' => 'Obat_dan_Ramuan_Tradisional',
        'baris-berbaris-tongkat' => 'Baris_Berbaris_Tongkat',
        'senam-pramuka' => 'Senam_Pramuka',
        'e-sport' => 'E-Sport',
        'robotik' => 'Robotik',
        'coding' => 'Coding',
        'panjat-dinding' => 'Panjat_Dinding',
        'memanah' => 'Memanah',
        'renang' => 'Renang',
        'halang-rintang' => 'Halang_Rintang',
        'pidato' => 'Pidato',
        'melukis-poster' => 'Melukis_Poster',
        'hasta-karya' => 'Hasta_Karya',
        'reportase' => 'Reportase'
    ];

    public function index()
    {
        $error = null;
        $message = null;

        try {
            // Coba konek ke endpoint home Flask untuk cek status API
            $statusCheck = Http::timeout(5)->get('http://127.0.0.1:5000/');
            if (!$statusCheck->successful()) {
                $error = 'Flask API tidak merespons. Pastikan API berjalan untuk melihat rekomendasi.';
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $error = 'Tidak dapat terhubung ke Flask API. Pastikan API berjalan dan dapat diakses.';
        } catch (\Exception $e) {
            $error = 'Terjadi kesalahan internal saat memeriksa status API: ' . $e->getMessage();
        }
        
        // Kirim semua data lomba ke view untuk ditampilkan dalam card
        return view('pembina.rekomendasi.index', compact('error', 'message'))
                    ->with('allLombas', $this->allLombas);
    }

    protected function getCompetitionsData()
    {
        // Data ini tetap ada untuk kebutuhan jumlah siswa yang dibutuhkan
        return [
            ['Lomba' => 'Pionering', 'Jumlah Siswa yang Dibutuhkan' => 6, 'Variabel yang Digunakan' => ['Matematika', 'IPA', 'Skor Penerapan', 'Status SKU', 'Hasta Karya']],
            ['Lomba' => 'Administrasi_Regu', 'Jumlah Siswa yang Dibutuhkan' => 2, 'Variabel yang Digunakan' => ['IPS', 'Bahasa Indonesia', 'Kehadiran', 'Status SKU']],
            ['Lomba' => 'Packing_Pengembaraan', 'Jumlah Siswa yang Dibutuhkan' => 1, 'Variabel yang Digunakan' => ['IPA', 'Skor Penerapan', 'Status SKU', 'Kehadiran']],
            ['Lomba' => 'Semboyan_dan_Isyarat', 'Jumlah Siswa yang Dibutuhkan' => 7, 'Variabel yang Digunakan' => ['Bahasa Indonesia', 'Skor Penerapan', 'Status SKU']],
            ['Lomba' => 'Sketsa_Panorama', 'Jumlah Siswa yang Dibutuhkan' => 2, 'Variabel yang Digunakan' => ['IPS', 'Bahasa Indonesia', 'Hasta Karya']],
            ['Lomba' => 'Peta_Pita_dan_Peta_Perjalanan', 'Jumlah Siswa yang Dibutuhkan' => 8, 'Variabel yang Digunakan' => ['Matematika', 'IPS', 'Skor Penerapan', 'Status SKU']],
            ['Lomba' => 'Menaksir', 'Jumlah Siswa yang Dibutuhkan' => 8, 'Variabel yang Digunakan' => ['Matematika', 'IPA']],
            ['Lomba' => 'Pertolongan_Pertama', 'Jumlah Siswa yang Dibutuhkan' => 8, 'Variabel yang Digunakan' => ['IPA', 'Skor Penerapan', 'Status TKK', 'Pencapaian TKK']],
            ['Lomba' => 'Masak_Rimba', 'Jumlah Siswa yang Dibutuhkan' => 3, 'Variabel yang Digunakan' => ['IPA', 'Status TKK', 'Pencapaian TKK']],
            ['Lomba' => 'Bivak', 'Jumlah Siswa yang Dibutuhkan' => 3, 'Variabel yang Digunakan' => ['IPA', 'Skor Penerapan', 'Status SKU']],
            ['Lomba' => 'Obat_dan_Ramuan_Tradisional', 'Jumlah Siswa yang Dibutuhkan' => 2, 'Variabel yang Digunakan' => ['IPA', 'IPS', 'Status TKK', 'Pencapaian TKK']],
            ['Lomba' => 'Baris_Berbaris_Tongkat', 'Jumlah Siswa yang Dibutuhkan' => 8, 'Variabel yang Digunakan' => ['Olahraga', 'Skor Penerapan', 'Status SKU']],
            ['Lomba' => 'Senam_Pramuka', 'Jumlah Siswa yang Dibutuhkan' => 8, 'Variabel yang Digunakan' => ['Olahraga', 'Skor Penerapan']],
            ['Lomba' => 'E-Sport', 'Jumlah Siswa yang Dibutuhkan' => 5, 'Variabel yang Digunakan' => ['Skor Tes TIK', 'Olahraga', 'Kehadiran']],
            ['Lomba' => 'Robotik', 'Jumlah Siswa yang Dibutuhkan' => 2, 'Variabel yang Digunakan' => ['Skor Tes TIK', 'IPA', 'Matematika', 'Status Tes TIK']],
            ['Lomba' => 'Coding', 'Jumlah Siswa yang Dibutuhkan' => 2, 'Variabel yang Digunakan' => ['Skor Tes TIK', 'Status Tes TIK', 'Matematika', 'Skor Penerapan']],
            ['Lomba' => 'Panjat_Dinding', 'Jumlah Siswa yang Dibutuhkan' => 1, 'Variabel yang Digunakan' => ['Olahraga', 'IPA', 'Skor Penerapan']],
            ['Lomba' => 'Memanah', 'Jumlah Siswa yang Dibutuhkan' => 1, 'Variabel yang Digunakan' => ['Olahraga', 'Matematika', 'Skor Penerapan']],
            ['Lomba' => 'Renang', 'Jumlah Siswa yang Dibutuhkan' => 1, 'Variabel yang Digunakan' => ['Olahraga', 'Skor Penerapan']],
            ['Lomba' => 'Halang_Rintang', 'Jumlah Siswa yang Dibutuhkan' => 1, 'Variabel yang Digunakan' => ['Olahraga', 'Kehadiran', 'Skor Penerapan', 'Status SKU']],
            ['Lomba' => 'Pidato', 'Jumlah Siswa yang Dibutuhkan' => 1, 'Variabel yang Digunakan' => ['Bahasa Indonesia', 'Bahasa Inggris', 'Skor Tes Bahasa', 'Status Tes Bahasa', 'Status SKU']],
            ['Lomba' => 'Melukis_Poster', 'Jumlah Siswa yang Dibutuhkan' => 1, 'Variabel yang Digunakan' => ['Hasta Karya', 'Bahasa Indonesia', 'Status SKU']],
            ['Lomba' => 'Hasta_Karya', 'Jumlah Siswa yang Dibutuhkan' => 4, 'Variabel yang Digunakan' => ['Hasta Karya', 'Skor Penerapan', 'Status SKU']],
            ['Lomba' => 'Reportase', 'Jumlah Siswa yang Dibutuhkan' => 2, 'Variabel yang Digunakan' => ['Bahasa Indonesia', 'Bahasa Inggris', 'Skor Tes Bahasa', 'Status Tes Bahasa']]
        ];
    }

    public function showByLomba($lombaSlug)
    {
        // Map slug ke nama lomba asli
        $lombaName = $this->allLombas[$lombaSlug] ?? null;

        if (!$lombaName) {
            return view('pembina.rekomendasi.lomba_detail')->with('error', 'Lomba tidak ditemukan.');
        }

        $rekomendasi = [];
        $error = null;
        $message = null;

        try {
            // Cek status API Flask terlebih dahulu
            $statusCheck = Http::timeout(5)->get('http://127.0.0.1:5000/');
            if (!$statusCheck->successful()) {
                $error = 'Flask API tidak merespons. Pastikan API berjalan.';
            } else {
                // Encode nama lomba asli sebelum dikirim ke Flask
                $encodedLombaName = urlencode(trim($lombaName));
                $response = Http::timeout(60)->get($this->flaskApiUrl . '/' . $encodedLombaName);

                if ($response->successful()) {
                    $rekomendasi = $response->json();
                    if (isset($rekomendasi['message']) && str_contains($rekomendasi['message'], 'Tidak ada rekomendasi')) {
                        $message = $rekomendasi['message'];
                        $rekomendasi = []; // Pastikan ini array kosong jika tidak ada rekomendasi
                    } elseif (!is_array($rekomendasi)) {
                        $error = 'Format data tidak sesuai dari API Flask.';
                        $rekomendasi = [];
                    }
                } else {
                    $errorResponse = $response->json();
                    $error = $errorResponse['error'] ?? 'Gagal mengambil rekomendasi untuk lomba ini. Status: ' . $response->status();
                }
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $error = 'Tidak dapat terhubung ke Flask API. Pastikan API berjalan dan dapat diakses.';
        } catch (\Exception $e) {
            $error = 'Terjadi kesalahan internal: ' . $e->getMessage();
        }

        // Ambil jumlah siswa yang dibutuhkan dari data kompetisi
        $lombaRequirements = collect($this->getCompetitionsData())->keyBy('Lomba');
        $requiredNum = $lombaRequirements->get($lombaName)['Jumlah Siswa yang Dibutuhkan'] ?? 0;

        return view('pembina.rekomendasi.lomba_detail', compact('rekomendasi', 'lombaName', 'requiredNum', 'error', 'message'));
    }
}