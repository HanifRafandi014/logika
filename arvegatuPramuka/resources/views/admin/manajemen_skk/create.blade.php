@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Tambah Data SKK</h4>
        </div>
        <div class="card-body">
            {{-- Tambahkan enctype="multipart/form-data" untuk memungkinkan upload file --}}
            <form action="{{ route('manajemen_skk.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="jenis_skk" class="form-label">Jenis SKK</label>
                    <input type="text" class="form-control" id="jenis_skk" name="jenis_skk" value="{{ old('jenis_skk') }}" required>
                    @error('jenis_skk')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Input untuk mengunggah file logo --}}
                <div class="mb-3">
                    <label for="logo" class="form-label">Upload Logo</label>
                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                    <small class="form-text text-muted">Format yang diizinkan: JPEG, PNG, JPG, GIF, SVG. Ukuran maksimal: 2MB.</small>
                    @error('logo')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="kompetensi_dasar" class="form-label">Kompetensi Dasar SKK</label>
                    <textarea class="form-control" id="kompetensi_dasar" name="kompetensi_dasar" rows="5" required>{{ old('kompetensi_dasar') }}</textarea>
                    @error('kompetensi_dasar')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="keterangan_skk" class="form-label">Item Pencapaian SKK</label>
                    <textarea class="form-control" id="keterangan_skk" name="keterangan_skk" rows="5" required>{{ old('keterangan_skk') }}</textarea>
                    @error('keterangan_skk')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="kelompok" class="form-label">Kelompok</label>
                    <select class="form-control" id="kelompok" name="kelompok" required>
                        <option value="">Pilih kelompok</option>
                        @foreach($kelompoks as $kelompok)
                            <option value="{{ $kelompok }}" {{ old('kelompok') == $kelompok ? 'selected' : '' }}>{{ $kelompok }}</option>
                        @endforeach
                    </select>
                    @error('kelompok')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="kategori" class="form-label">Kategori</label>
                    <select class="form-control" id="kategori" name="kategori" required>
                        <option value="">Pilih kategori</option>
                        @foreach($kategoris as $kategori)
                            <option value="{{ $kategori }}" {{ old('kategori') == $kategori ? 'selected' : '' }}>{{ $kategori }}</option>
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
                            <option value="{{ $tingkatan }}" {{ old('tingkatan') == $tingkatan ? 'selected' : '' }}>{{ $tingkatan }}</option>
                        @endforeach
                    </select>
                    @error('tingkatan')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('manajemen_skk.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
