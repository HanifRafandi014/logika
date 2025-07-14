<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Lomba;
use Illuminate\Support\Facades\Log;

class RecommendationController extends Controller
{
    protected $flaskApiUrl = 'http://127.0.0.1:5000/api/recommendations';

    public function index()
    {
        $error = null;
        $message = null;

        try {
            $statusCheck = Http::timeout(5)->get('http://127.0.0.1:5000/');
            if (!$statusCheck->successful()) {
                $error = 'Flask API tidak merespons. Pastikan API berjalan untuk melihat rekomendasi.';
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $error = 'Tidak dapat terhubung ke Flask API. Pastikan API berjalan dan dapat diakses.';
        } catch (\Exception $e) {
            $error = 'Terjadi kesalahan internal saat memeriksa status API: ' . $e->getMessage();
        }

        // Ambil semua lomba aktif dan bentuk mapping slug → nama lomba
        $allLombas = [];
        $lombas = Lomba::with('variabel')->where('status', 1)->get();
        foreach ($lombas as $lomba) {
            if ($lomba->variabelClustering) {
                $jenis = $lomba->variabelClustering->jenis_lomba;
                $slug = strtolower(str_replace([' ', '_'], '-', $jenis));
                $allLombas[$slug] = $jenis;
            }
        }

        return view('pembina.rekomendasi.index', compact('error', 'message', 'allLombas'));
    }

    protected function getCompetitionsData()
    {
        $data = [];

        $lombas = Lomba::with('variabelClustering')->where('status', 1)->get();

        foreach ($lombas as $lomba) {
            $clustering = $lomba->variabelClustering;

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
        // Ambil semua data lomba untuk mapping slug → jenis lomba
        $allLombas = [];
        $lombas = Lomba::with('variabelClustering')->where('status', 1)->get();
        foreach ($lombas as $lomba) {
            if ($lomba->variabelClustering) {
                $jenis = $lomba->variabelClustering->jenis_lomba;
                $slug = strtolower(str_replace([' ', '_'], '-', $jenis));
                $allLombas[$slug] = $jenis;
            }
        }

        $lombaName = $allLombas[$lombaSlug] ?? null;

        if (!$lombaName) {
            return view('pembina.rekomendasi.lomba_detail')->with('error', 'Lomba tidak ditemukan.');
        }

        $rekomendasi = [];
        $error = null;
        $message = null;

        try {
            $statusCheck = Http::timeout(5)->get('http://127.0.0.1:5000/');
            if (!$statusCheck->successful()) {
                $error = 'Flask API tidak merespons. Pastikan API berjalan.';
            } else {
                $encodedLombaName = urlencode(trim($lombaName));
                $response = Http::timeout(60)->get($this->flaskApiUrl . '/' . $encodedLombaName);

                if ($response->successful()) {
                    $rekomendasi = $response->json();
                    if (isset($rekomendasi['message']) && str_contains($rekomendasi['message'], 'Tidak ada rekomendasi')) {
                        $message = $rekomendasi['message'];
                        $rekomendasi = [];
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

        // Ambil jumlah siswa dari DB
        $requiredNum = $lombas->firstWhere(fn ($l) => $l->variabelClustering && $l->variabelClustering->jenis_lomba === $lombaName)?->jumlah_siswa ?? 0;

        return view('pembina.rekomendasi.lomba_detail', compact('rekomendasi', 'lombaName', 'requiredNum', 'error', 'message'));
    }
}
