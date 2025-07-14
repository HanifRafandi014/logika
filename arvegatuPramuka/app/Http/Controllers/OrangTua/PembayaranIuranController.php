<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\PembayaranSpp;
use App\Models\Siswa;
use App\Models\OrangTua;
use App\Models\BesaranBiaya;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PembayaranIuranController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'orang_tua' || !$user->orang_tua) {
            abort(403, 'Unauthorized');
        }

        $orangTua = $user->orang_tua;
        $siswa = $orangTua->siswa;

        if (!$siswa) {
            return redirect()->back()->with('error', 'Akun ini belum dikaitkan dengan siswa.');
        }

        $besaranBiaya = BesaranBiaya::first();
        if (!$besaranBiaya) {
            return redirect()->back()->with('error', 'Besaran biaya belum ditentukan.');
        }

        $bulanDalamTahun = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // Dapatkan tahun sekarang
        $tahunSekarang = date('Y');

        // Ambil semua pembayaran orang tua ini
        $pembayaranSpps = PembayaranSpp::where('orang_tua_id', $orangTua->id)
            ->with('siswa')
            ->orderBy('created_at', 'desc')
            ->get();

        // Tandai bulan mana saja yang sudah dibayar
        $statusBulanan = [];
        $totalSudahDibayar = 0; // ← Hitung total yang sudah dibayar

        foreach ($pembayaranSpps as $pembayaran) {
            $bulanBayar = json_decode($pembayaran->bulan_bayar, true);
            if (is_array($bulanBayar)) {
                $jumlahBulan = count($bulanBayar);

                // Tambahkan ke total yang sudah dibayar
                $totalSudahDibayar += $jumlahBulan * $besaranBiaya->total_biaya;

                foreach ($bulanBayar as $bulanAngka) {
                    $statusBulanan[(int)$bulanAngka] = [
                        'status' => $pembayaran->status_pembayaran,
                        'bukti_bayar' => $pembayaran->bukti_bayar,
                        'id' => $pembayaran->id,
                        'tahun' => $tahunSekarang,
                    ];
                }
            }
        }

        $besaranBiayaTotal = $besaranBiaya ? $besaranBiaya->total_biaya : 0;

        return view('orang_tua.pembayaran_iuran.index', compact(
            'orangTua',
            'siswa',
            'besaranBiaya',
            'bulanDalamTahun',
            'statusBulanan',
            'pembayaranSpps',
            'besaranBiayaTotal',
            'tahunSekarang',
            'totalSudahDibayar' // ← Kirim ke blade
        ));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('pembayaran-iuran.index')
            ->with('info', 'Silakan lakukan pembayaran dari halaman utama.');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'orang_tua' || !$user->orang_tua) {
            abort(403, 'Unauthorized.');
        }

        $orangTua = $user->orang_tua;
        $siswa = $orangTua->siswa;

        if (!$siswa) {
            return redirect()->back()->with('error', 'Siswa tidak ditemukan untuk akun Anda.');
        }

        $request->validate([
            'bulan_bayar' => 'required|array',
            'bulan_bayar.*' => 'required|integer|min:1|max:12',
            'bukti_bayar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Simpan bukti bayar jika diunggah
        $buktiBayarPath = null;
        if ($request->hasFile('bukti_bayar')) {
            $buktiBayarPath = $request->file('bukti_bayar')->store('bukti_pembayaran', 'public');
        }

        $besaranBiaya = BesaranBiaya::first();
        if (!$besaranBiaya) {
            return redirect()->back()->with('error', 'Besaran biaya belum tersedia.');
        }

        PembayaranSpp::create([
            'siswa_id' => $siswa->id,
            'orang_tua_id' => $orangTua->id,
            'besaran_biaya_id' => $besaranBiaya->id,
            'bulan_bayar' => json_encode($request->bulan_bayar),
            'bukti_bayar' => $buktiBayarPath,
            'status_pembayaran' => 0, // Default belum diverifikasi
            // Jika Anda ingin menyimpan tahun pembayaran di database, tambahkan kolom 'tahun'
            // dan masukkan $tahunSekarang di sini. Contoh: 'tahun_bayar' => date('Y'),
        ]);

        return redirect()->route('pembayaran-iuran.index')->with('success', 'Pembayaran berhasil ditambahkan.');
    }

    public function riwayatPembayaran(): View
    {
        $loggedInUser = Auth::user();

        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || !$loggedInUser->orang_tua) {
            abort(403, 'Unauthorized. Anda harus login sebagai orang tua untuk melihat riwayat pembayaran.');
        }

        $orangTuaId = $loggedInUser->orang_tua->id;

        $riwayatPembayarans = PembayaranSpp::with('siswa', 'besaran_biaya')
            ->where('orang_tua_id', $orangTuaId)
            ->latest()
            ->get();

        return view('orang_tua.pembayaran_iuran.riwayat_pembayaran', compact('riwayatPembayarans'));
    }

public function verifikasiIndex(Request $request): View|RedirectResponse
    {
        $loggedInUser = Auth::user();
        if (
            !$loggedInUser ||
            $loggedInUser->role !== 'orang_tua' ||
            !$loggedInUser->orang_tua ||
            $loggedInUser->orang_tua->status !== 'Pengurus Paguyuban Kelas'
        ) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengakses halaman verifikasi pembayaran.');
        }

        $pengurusSiswa = $loggedInUser->orang_tua->siswa;
        if (!$pengurusSiswa) {
            return redirect()->back()->with('error', 'Akun pengurus ini belum terkait dengan siswa.');
        }

        $kelasPengurus = $pengurusSiswa->kelas;
        if (empty($kelasPengurus)) {
            return redirect()->back()->with('error', 'Siswa yang terkait dengan akun pengurus ini belum memiliki informasi kelas.');
        }

        $siswaFilter = $request->query('siswa');
        $orangTuaFilter = $request->query('orang_tua');

        $pembayaranUntukVerifikasiQuery = PembayaranSpp::with(['siswa', 'orang_tua'])
            ->where('status_pembayaran', 0)
            ->whereHas('siswa', function ($query) use ($kelasPengurus) {
                $query->where('kelas', $kelasPengurus);
            });

        if ($siswaFilter && $orangTuaFilter) {
            $pembayaranUntukVerifikasiQuery->whereHas('siswa', function ($query) use ($siswaFilter) {
                $query->where('nama', $siswaFilter);
            })->whereHas('orang_tua', function ($query) use ($orangTuaFilter) {
                $query->where('nama', $orangTuaFilter);
            });
        }

        $pembayaranUntukVerifikasi = $pembayaranUntukVerifikasiQuery->latest()->get();

        // Tambahkan array mapping bulan ke data yang dikirimkan ke view
        $bulanDalamTahun = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('orang_tua.pengurus_kelas.verifikasi_pembayaran_kelas', [
            'pembayaranUntukVerifikasi' => $pembayaranUntukVerifikasi,
            'loggedInUser' => $loggedInUser,
            'kelasPengurus' => $kelasPengurus,
            'bulanDalamTahun' => $bulanDalamTahun // <-- Tambahkan ini
        ]);
    }

    public function verify(Request $request, int $id): RedirectResponse
    {
        $loggedInUser = Auth::user();

        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || !$loggedInUser->orang_tua || $loggedInUser->orang_tua->status !== 'Pengurus Paguyuban Kelas') {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk memverifikasi pembayaran.');
        }

        $pembayaran = PembayaranSpp::find($id);

        if (!$pembayaran) {
            return redirect()->back()->with('error', 'Pembayaran tidak ditemukan.');
        }

        if ($pembayaran->status_pembayaran === 1) {
            return redirect()->back()->with('info', 'Pembayaran ini sudah diverifikasi sebelumnya.');
        }

        $pengurusSiswa = $loggedInUser->orang_tua->siswa;
        if (!$pengurusSiswa || $pengurusSiswa->kelas_id !== $pembayaran->siswa->kelas_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk memverifikasi pembayaran di luar kelas Anda.');
        }

        $pembayaran->status_pembayaran = 1;
        $pembayaran->save();

        return redirect()->back()->with('success', 'Pembayaran berhasil diverifikasi.');
    }
}