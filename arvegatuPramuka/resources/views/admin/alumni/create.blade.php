<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
    <div class="card">
      <div class="card-header">
        <h4>Tambah Alumni</h4>
      </div>
      <div class="card-body">
        <form action="{{ route('data-alumni.store') }}" method="POST">
          @csrf
          <div class="form-group">
            <label for="siswa_id">Nama Alumni</label>
            <select name="siswa_id" id="siswa_id" class="form-control @error('siswa_id') is-invalid @enderror" required>
                <option value="">Pilih Siswa</option>
                @foreach ($siswas as $siswa)
                    <option value="{{ $siswa->id }}" {{ old('siswa_id') == $siswa->id ? 'selected' : '' }}>
                        {{ $siswa->nama }} (Angkatan: {{ $siswa->angkatan }})
                    </option>
                @endforeach
            </select>
            @error('siswa_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
          </div>
          <div class="form-group">
            <label for="tahun_lulus">Tahun Lulus</label>
            {{-- Menggunakan type="number" dan default value tahun saat ini --}}
            <input type="number" name="tahun_lulus" id="tahun_lulus" class="form-control @error('tahun_lulus') is-invalid @enderror" value="{{ old('tahun_lulus', date('Y')) }}" required>
            @error('tahun_lulus')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
          </div>
          <div class="form-group">
            <label for="pekerjaan">Pekerjaan</label>
            <input type="text" name="pekerjaan" id="pekerjaan" class="form-control @error('pekerjaan') is-invalid @enderror" value="{{ old('pekerjaan') }}" required>
            @error('pekerjaan')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
          </div>
          <div class="form-group">
            <label for="no_hp">No Handphone</label>
            <input type="text" name="no_hp" id="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp') }}" required>
            @error('no_hp')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
          </div>
          <button type="submit" class="btn btn-primary" title="Simpan Data">
            <i class="fas fa-save"></i>
          </button>
          <a href="{{ route('data-alumni.index') }}" class="btn btn-secondary" title="Kembali">
            <i class="fas fa-arrow-left"></i>
          </a>
        </form>
      </div>
    </div>
  </div>
@endsection
