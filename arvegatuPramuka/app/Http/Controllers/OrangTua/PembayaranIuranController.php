<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\PembayaranSpp;
use App\Models\Siswa;
use App\Models\OrangTua;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PembayaranIuranController extends Controller
{
    public function index()
    {
        $loggedInUser = Auth::user();

        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || !$loggedInUser->orang_tua) {
            abort(403, 'Unauthorized. You must be a logged-in parent to view this page.');
        }

        $orangTuaId = $loggedInUser->orang_tua->id;

        $pembayaranSpps = PembayaranSpp::with(['siswa', 'orang_tua'])
                                ->where('orang_tua_id', $orangTuaId)
                                ->latest()
                                ->get();

        return view('orang_tua.pembayaran_iuran.index', compact('pembayaranSpps'));
    }

    public function create()
    {
        $loggedInUser = Auth::user();

        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || !$loggedInUser->orang_tua) {
            abort(403, 'Unauthorized. You must be a logged-in parent to create a payment.');
        }

        $orangTuaLogin = $loggedInUser->orang_tua;
        $siswas = Siswa::all(); // Mengambil semua siswa untuk dropdown

        return view('orang_tua.pembayaran_iuran.create', compact('orangTuaLogin', 'siswas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bulan_bayar' => 'required|date',
            'jumlah' => 'required|integer',
            'bukti_bayar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status_pembayaran' => 'boolean', // Mengubah dari nullable|boolean menjadi boolean saja
            'siswa_id' => 'required|exists:siswas,id',
        ]);

        $buktiBayarPath = null;
        if ($request->hasFile('bukti_bayar')) {
            $buktiBayarPath = $request->file('bukti_bayar')->store('bukti_pembayaran', 'public');
        }

        $loggedInUser = Auth::user();

        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || !$loggedInUser->orang_tua) {
            abort(403, 'Unauthorized.');
        }

        $orangTuaId = $loggedInUser->orang_tua->id;

        PembayaranSpp::create([
            'bulan_bayar' => $request->bulan_bayar,
            'jumlah' => $request->jumlah,
            'bukti_bayar' => $buktiBayarPath,
            'status_pembayaran' => $request->has('status_pembayaran') ? 1 : 0, // Jika ada di request (dicentang) = 1, jika tidak = 0
            'siswa_id' => $request->siswa_id,
            'orang_tua_id' => $orangTuaId,
        ]);

        return redirect()->route('pembayaran-iuran.index')->with('success', 'Pembayaran iuran berhasil ditambahkan.');
    }

    public function edit(PembayaranSpp $pembayaranIuran)
    {
        $loggedInUser = Auth::user();

        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || !$loggedInUser->orang_tua) {
            abort(403, 'Unauthorized.');
        }

        if ($loggedInUser->orang_tua->id !== $pembayaranIuran->orang_tua_id) {
            abort(403, 'Unauthorized action.');
        }

        $orangTuaLogin = $pembayaranIuran->orang_tua;
        $siswas = Siswa::all(); // Mengambil semua siswa untuk dropdown di form edit

        return view('orang_tua.pembayaran_iuran.edit', compact('pembayaranIuran', 'orangTuaLogin', 'siswas'));
    }

    public function update(Request $request, PembayaranSpp $pembayaranIuran)
    {
        $loggedInUser = Auth::user();

        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || !$loggedInUser->orang_tua) {
            abort(403, 'Unauthorized.');
        }

        if ($loggedInUser->orang_tua->id !== $pembayaranIuran->orang_tua_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'bulan_bayar' => 'required|date',
            'jumlah' => 'required|integer',
            'bukti_bayar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status_pembayaran' => 'boolean', // Mengubah dari nullable|boolean menjadi boolean saja
            'siswa_id' => 'required|exists:siswas,id',
        ]);

        $buktiBayarPath = $pembayaranIuran->bukti_bayar;
        if ($request->hasFile('bukti_bayar')) {
            if ($buktiBayarPath) {
                Storage::disk('public')->delete($buktiBayarPath);
            }
            $buktiBayarPath = $request->file('bukti_bayar')->store('bukti_pembayaran', 'public');
        } elseif ($request->boolean('remove_bukti_bayar')) { // Ini adalah cara untuk menghapus bukti bayar jika checkbox hapus dicentang
            if ($buktiBayarPath) {
                Storage::disk('public')->delete($buktiBayarPath);
            }
            $buktiBayarPath = null;
        }

        $orangTuaId = $loggedInUser->orang_tua->id;

        $pembayaranIuran->update([
            'bulan_bayar' => $request->bulan_bayar,
            'jumlah' => $request->jumlah,
            'bukti_bayar' => $buktiBayarPath,
            'status_pembayaran' => $request->has('status_pembayaran') ? 1 : 0, // Jika ada di request (dicentang) = 1, jika tidak = 0
            'siswa_id' => $request->siswa_id,
            'orang_tua_id' => $orangTuaId,
        ]);

        return redirect()->route('pembayaran-iuran.index')->with('success', 'Pembayaran iuran berhasil diperbarui.');
    }

    public function destroy(PembayaranSpp $pembayaranIuran)
    {
        $loggedInUser = Auth::user();

        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || !$loggedInUser->orang_tua) {
            abort(403, 'Unauthorized.');
        }

        if ($loggedInUser->orang_tua->id !== $pembayaranIuran->orang_tua_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($pembayaranIuran->bukti_bayar) {
            Storage::disk('public')->delete($pembayaranIuran->bukti_bayar);
        }
        $pembayaranIuran->delete();
        return redirect()->route('pembayaran-iuran.index')->with('success', 'Pembayaran iuran berhasil dihapus.');
    }

    public function riwayatPembayaran()
    {
        $loggedInUser = Auth::user();

        if (!$loggedInUser || $loggedInUser->role !== 'orang_tua' || !$loggedInUser->orang_tua) {
            abort(403, 'Unauthorized. You must be a logged-in parent to view payment history.');
        }

        $orangTuaId = $loggedInUser->orang_tua->id;

        // Fetch all payment records for the logged-in parent, eager load siswa relation
        $riwayatPembayarans = PembayaranSpp::with('siswa')
                                            ->where('orang_tua_id', $orangTuaId)
                                            ->latest() // Order by latest payments first
                                            ->get();

        return view('orang_tua.pembayaran_iuran.riwayat_pembayaran', compact('riwayatPembayarans'));
    }
}
