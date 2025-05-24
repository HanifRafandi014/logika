<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Event;

class ManajemenEvent extends Controller
{
    public function index() {
        $event = Event::all();
    return view('admin.event.index', compact('event'));
    } 

    public function create(){
        return view('admin.event.create');
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'nama_alumni' => 'required',
            'jenis_event' => 'required',
            'judul' => 'required',
            'gambar' => 'required',
            'keterangan' => 'required',
        ]);
        $validatedData['nama'] =strtoupper(trim($validatedData['nama']));
        Event::create($validatedData);
        return redirect()->route('admin.event.index')->with('success', 'Data berhasil ditambahkan!');
    }
    public function edit($id){
        $event = Event::findOrFail($id);
        return view('admin.event.edit', compact('event'));
    }
    public function update(Request $request,  $id){
        $validatedData = $request->validate([
            'nama_alumni' => 'required',
            'jenis_event' => 'required',
            'judul' => 'required',
            'gambar' => 'required',
            'keterangan' => 'required',
        ]);

        $event = Event::findOrFail($id);
        $event->update($validatedData);
        return redirect()->route('admin.event.index')->with('success', 'Data berhasil diubah!');
    }
    public function destroy($id){
        Event::destroy($id);
        return redirect()->route('admin.event.index')->with('success', 'Data berhasil dihapus!');
    }
}
