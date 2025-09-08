<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\Event; // Asumsi ini adalah model untuk tabel 'event_alumni'
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Import Storage facade

class ManajemenEvent extends Controller
{
    public function index()
    {
        // Ambil alumni yang sedang login
        $alumni = Auth::user()->alumni;

        if (!$alumni) {
            return redirect()->back()->with('error', 'Profil alumni tidak ditemukan. Anda tidak dapat melihat event.');
        }

        // Ambil event yang dibuat oleh alumni yang login
        $events = Event::where('alumni_id', $alumni->id)->latest()->get();

        return view('alumni.event.index', compact('events'));
    }

    public function create()
    {
        // Pastikan alumni sudah login dan terdaftar
        if (!Auth::check() || !Auth::user()->alumni) {
            return redirect()->back()->with('error', 'Anda harus login sebagai alumni untuk membuat event.');
        }
        return view('alumni.event.create');
    }

    public function store(Request $request)
    {
        // Pastikan alumni sudah login dan terdaftar
        $alumni = Auth::user()->alumni;
        if (!$alumni) {
            return redirect()->back()->with('error', 'Anda harus login sebagai alumni untuk membuat event.');
        }

        $validatedData = $request->validate([
            'jenis_event' => 'required|string|max:255',
            'judul' => 'required|string|max:255',
            'gambar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'keterangan' => 'required|string',
        ]);

        // Handle file upload
        if ($request->hasFile('gambar')) {
            $imagePath = $request->file('gambar')->store('public/event_images');
            $validatedData['gambar'] = str_replace('public/', '', $imagePath);
        }

        // Buat event baru dan kaitkan dengan alumni yang login
        Event::create([
            'jenis_event' => $validatedData['jenis_event'],
            'judul' => $validatedData['judul'],
            'gambar' => $validatedData['gambar'],
            'keterangan' => $validatedData['keterangan'],
            'alumni_id' => $alumni->id, // Otomatis mengisi alumni_id
        ]);

        return redirect()->route('event.index')->with('success', 'Event berhasil ditambahkan!');
    }

    public function edit(Event $event)
    {
        // Pastikan event ini milik alumni yang sedang login
        if ($event->alumni_id !== Auth::user()->alumni->id) {
            return redirect()->route('event.index')->with('error', 'Anda tidak memiliki akses untuk mengedit event ini.');
        }
        return view('alumni.event.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        // Pastikan event ini milik alumni yang sedang login
        if ($event->alumni_id !== Auth::user()->alumni->id) {
            return redirect()->route('event.index')->with('error', 'Anda tidak memiliki akses untuk memperbarui event ini.');
        }

        $validatedData = $request->validate([
            'jenis_event' => 'required|string|max:255',
            'judul' => 'required|string|max:255',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'keterangan' => 'required|string',
        ]);

        // Handle file upload jika ada gambar baru
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($event->gambar && Storage::exists('public/' . $event->gambar)) {
                Storage::delete('public/' . $event->gambar);
            }
            $imagePath = $request->file('gambar')->store('public/event_images');
            $validatedData['gambar'] = str_replace('public/', '', $imagePath);
        }

        $event->update($validatedData);
        return redirect()->route('event.index')->with('success', 'Event berhasil diperbarui!');
    }

    public function destroy(Event $event)
    {
        // Pastikan event ini milik alumni yang sedang login
        if ($event->alumni_id !== Auth::user()->alumni->id) {
            return redirect()->route('event.index')->with('error', 'Anda tidak memiliki akses untuk menghapus event ini.');
        }

        // Hapus file gambar dari storage
        if ($event->gambar && Storage::exists('public/' . $event->gambar)) {
            Storage::delete('public/' . $event->gambar);
        }

        $event->delete();
        return redirect()->route('event.index')->with('success', 'Event berhasil dihapus!');
    }
}
