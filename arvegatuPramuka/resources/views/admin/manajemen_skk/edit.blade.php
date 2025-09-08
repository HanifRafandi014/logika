@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Edit Data SKK</h4>
        </div>
        <div class="card-body">
            {{-- PERBAIKAN: Pastikan ada enctype="multipart/form-data" --}}
            <form action="{{ route('manajemen_skk.update', $skk->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT') 
                <div class="mb-3">
                    <label for="jenis_skk" class="form-label">Jenis SKK</label>
                    <input type="text" class="form-control @error('jenis_skk') is-invalid @enderror" id="jenis_skk" name="jenis_skk" value="{{ old('jenis_skk', $skk->jenis_skk) }}" required>
                    @error('jenis_skk')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Input untuk Logo --}}
                <div class="mb-3">
                    <label for="logo" class="form-label">Upload Logo Baru (Opsional)</label>
                    <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/*">
                    <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah logo. Format yang diizinkan: JPEG, PNG, JPG, GIF, SVG. Ukuran maksimal: 2MB.</small>
                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if($skk->logo)
                        <div class="mt-2">
                            <strong>Logo Saat Ini:</strong><br>
                            <img src="{{ asset('storage/' . $skk->logo) }}" alt="Logo SKK Saat Ini" style="max-width: 150px; height: auto; border-radius: 8px;">
                        </div>
                    @else
                        <div class="mt-2">
                            <strong>Tidak ada logo saat ini.</strong>
                        </div>
                    @endif
                </div>

                {{-- Textarea untuk Kompetensi Dasar --}}
                <div class="mb-3">
                    <label for="kompetensi_dasar" class="form-label">Kompetensi Dasar SKK</label>
                    <textarea class="form-control @error('kompetensi_dasar') is-invalid @enderror" id="kompetensi_dasar" name="kompetensi_dasar" rows="5" required>{{ old('kompetensi_dasar', $skk->kompetensi_dasar) }}</textarea>
                    @error('kompetensi_dasar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="keterangan_skk" class="form-label">Item Pencapaian SKK</label>
                    <textarea class="form-control" id="keterangan_skk" name="keterangan_skk" rows="5" required>{{ old('keterangan_skk', $skk->keterangan_skk) }}</textarea>
                    @error('keterangan_skk')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                {{-- Perhatikan: Ini adalah "kelompok" yang Anda tambahkan sebelumnya, bukan "kategori" atau "keperluan" --}}
                <div class="mb-3">
                    <label for="kelompok" class="form-label">Kelompok</label>
                    <select class="form-control" id="kelompok" name="kelompok" required>
                        <option value="">Pilih kelompok</option>
                        @foreach($kelompoks as $kelompok)
                            <option value="{{ $kelompok }}" {{ old('kelompok', $skk->kelompok) == $kelompok ? 'selected' : '' }}>{{ $kelompok }}</option>
                        @endforeach
                    </select>
                    @error('kelompok')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Perhatikan: Ini adalah "kategori" yang Anda tambahkan sebelumnya, bukan "kelompok" atau "tingkatan" --}}
                <div class="mb-3">
                    <label for="kategori" class="form-label">Kategori</label>
                    <select class="form-control" id="kategori" name="kategori" required>
                        <option value="">Pilih kategori</option>
                        @foreach($kategoris as $kategori)
                            <option value="{{ $kategori }}" {{ old('kategori', $skk->kategori) == $kategori ? 'selected' : '' }}>{{ $kategori }}</option>
                        @endforeach
                    </select>
                    @error('kategori')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="tingkatan" class="form-label">Tingkatan</label>
                    <select class="form-control" id="tingkatan" name="tingkatan" required>
                        <option value="">Pilih Tingkatan</option>
                        @foreach($tingkatans as $tingkatan)
                            <option value="{{ $tingkatan }}" {{ old('tingkatan', $skk->tingkatan) == $tingkatan ? 'selected' : '' }}>{{ $tingkatan }}</option>
                        @endforeach
                    </select>
                    @error('tingkatan')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" title="Update">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <a href="{{ route('manajemen_skk.index') }}" class="btn btn-secondary" title="Kembali">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
