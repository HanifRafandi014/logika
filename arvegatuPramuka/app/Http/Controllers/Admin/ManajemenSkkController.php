<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManajemenSkk;
use Illuminate\Support\Facades\Storage; // Tambahkan ini untuk mengelola file

class ManajemenSkkController extends Controller
{
    /**
     * Menampilkan daftar semua SKK.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Mengambil semua data SKK dari database
        $skks = ManajemenSkk::all();
        // Mengembalikan view index dengan data SKK
        return view('admin.manajemen_skk.index', compact('skks'));
    }

    /**
     * Menampilkan form untuk membuat SKK baru.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Mendefinisikan pilihan untuk dropdown
        $tingkatans = ['Purwa', 'Madya', 'Utama'];
        $kelompoks = ['Agama', 'Patriotisme', 'Kesehatan', 'Keterampilan', 'Sosial'];
        $kategoris = ['Wajib', 'Keahlian'];
        // Mengembalikan view create dengan pilihan dropdown
        return view('admin.manajemen_skk.create', compact('tingkatans', 'kelompoks', 'kategoris'));
    }

    /**
     * Menyimpan SKK baru ke database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validasi data yang masuk dari request
        $validatedData = $request->validate([
            'jenis_skk' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validasi untuk upload gambar
            'kompetensi_dasar' => 'required|string',
            'keterangan_skk' => 'required|string',
            'tingkatan' => 'required|in:Purwa,Madya,Utama',
            'kelompok' => 'required|in:Agama,Patriotisme,Kesehatan,Keterampilan,Sosial',
            'kategori' => 'required|in:Wajib,Keahlian',
        ]);

        $logoPath = null;
        // Tangani upload file logo
        if ($request->hasFile('logo')) {
            // Simpan file ke direktori 'public/skk_logos'
            // dan dapatkan path yang disimpan
            $logoPath = $request->file('logo')->store('skk_logos', 'public');
        }

        // Membuat entri SKK baru di database
        $skk = ManajemenSkk::create([
            'jenis_skk' => $validatedData['jenis_skk'],
            'logo' => $logoPath, // Menyimpan path logo
            'kompetensi_dasar' => $validatedData['kompetensi_dasar'],
            'keterangan_skk' => $validatedData['keterangan_skk'],
            'tingkatan' => $validatedData['tingkatan'],
            'kelompok' => $validatedData['kelompok'],
            'kategori' => $validatedData['kategori'],
        ]);

        // Mengarahkan kembali ke halaman index dengan pesan sukses
        return redirect()->route('manajemen_skk.index')->with('success', 'Data berhasil ditambahkan!');
    }

    /**
     * Menampilkan detail SKK tertentu.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Mencari SKK berdasarkan ID, jika tidak ditemukan akan menghasilkan 404
        $skk = ManajemenSkk::findOrFail($id);
        // Mengembalikan view show dengan data SKK
        return view('admin.manajemen_skk.show', compact('skk'));
    }

    /**
     * Menampilkan form untuk mengedit SKK tertentu.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // Mencari SKK berdasarkan ID, jika tidak ditemukan akan menghasilkan 404
        $skk = ManajemenSkk::findOrFail($id);
        // Mendefinisikan pilihan untuk dropdown
        $tingkatans = ['Purwa', 'Madya', 'Utama'];
        $kelompoks = ['Agama', 'Patriotisme', 'Kesehatan', 'Keterampilan', 'Sosial'];
        $kategoris = ['Wajib', 'Keahlian'];
        // Mengembalikan view edit dengan data SKK dan pilihan dropdown
        return view('admin.manajemen_skk.edit', compact('skk', 'tingkatans', 'kelompoks', 'kategoris'));
    }

    /**
     * Memperbarui SKK tertentu di database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Mencari SKK berdasarkan ID
        $skk = ManajemenSkk::findOrFail($id);

        // Validasi data yang masuk dari request
        $rules = [
            'jenis_skk' => 'required|string',
            'kompetensi_dasar' => 'required|string',
            'keterangan_skk' => 'required|string',
            'tingkatan' => 'required|in:Purwa,Madya,Utama',
            'kelompok' => 'required|in:Agama,Patriotisme,Kesehatan,Keterampilan,Sosial',
            'kategori' => 'required|in:Wajib,Keahlian',
        ];

        // Jika ada file logo baru yang diupload, tambahkan aturan validasi untuk logo
        if ($request->hasFile('logo')) {
            $rules['logo'] = 'image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        }

        $validatedData = $request->validate($rules);

        $logoPath = $skk->logo; // Tetap gunakan logo yang sudah ada secara default

        // Tangani upload file logo baru
        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($skk->logo && Storage::disk('public')->exists($skk->logo)) {
                Storage::disk('public')->delete($skk->logo);
            }
            // Simpan file baru
            $logoPath = $request->file('logo')->store('skk_logos', 'public');
        }

        // Memperbarui entri SKK di database
        $skk->update([
            'jenis_skk' => $validatedData['jenis_skk'],
            'logo' => $logoPath, // Memperbarui path logo
            'kompetensi_dasar' => $validatedData['kompetensi_dasar'],
            'keterangan_skk' => $validatedData['keterangan_skk'],
            'tingkatan' => $validatedData['tingkatan'],
            'kelompok' => $validatedData['kelompok'],
            'kategori' => $validatedData['kategori'],
        ]);

        // Mengarahkan kembali ke halaman index dengan pesan sukses
        return redirect()->route('manajemen_skk.index')->with('success', 'Data berhasil diubah!');
    }

    /**
     * Menghapus SKK tertentu dari database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Mencari SKK berdasarkan ID
        $skk = ManajemenSkk::findOrFail($id);

        // Hapus file logo dari storage sebelum menghapus record dari database
        if ($skk->logo && Storage::disk('public')->exists($skk->logo)) {
            Storage::disk('public')->delete($skk->logo);
        }
        
        // Menghapus SKK
        $skk->delete();

        // Mengarahkan kembali ke halaman index dengan pesan sukses
        return redirect()->route('manajemen_skk.index')->with('success', 'Data berhasil dihapus!');
    }
}
