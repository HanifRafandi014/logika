@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
    <div class="card">
      <div class="card-header">
        <h4>Edit Data Alumni</h4>
      </div>
      <div class="card-body">
        <form action="{{ route('data-alumni.update', $alumni->id) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="form-group">
            <label for="siswa_id">Nama Alumni</label>
            <select name="siswa_id" id="siswa_id" class="form-control @error('siswa_id') is-invalid @enderror" required>
                <option value="">Pilih Siswa</option>
                @foreach ($siswas as $siswa)
                    <option value="{{ $siswa->id }}" {{ old('siswa_id', $alumni->siswa_id) == $siswa->id ? 'selected' : '' }}>
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
            <input type="number" name="tahun_lulus" id="tahun_lulus" class="form-control @error('tahun_lulus') is-invalid @enderror" value="{{ old('tahun_lulus', $alumni->tahun_lulus) }}" required>
            @error('tahun_lulus')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
          </div>
          <div class="form-group">
            <label for="pekerjaan">Pekerjaan</label>
            <input type="text" name="pekerjaan" id="pekerjaan" class="form-control @error('pekerjaan') is-invalid @enderror" value="{{ old('pekerjaan', $alumni->pekerjaan) }}" required>
            @error('pekerjaan')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
          </div>
          <div class="form-group">
            <label for="no_hp">No Handphone</label>
            <input type="text" name="no_hp" id="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp', $alumni->no_hp) }}" required>
            @error('no_hp')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
          </div>
          <button type="submit" class="btn btn-primary" title="Update">
            <i class="fas fa-sync-alt"></i>
          </button>
          <a href="{{ route('data-alumni.index') }}" class="btn btn-secondary" title="Kembali">
            <i class="fas fa-arrow-left"></i>
          </a>
        </form>
      </div>
    </div>
  </div>
@endsection
