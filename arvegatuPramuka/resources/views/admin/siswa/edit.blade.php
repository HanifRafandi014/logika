@extends('layouts.main')

@section('sidebar')
  @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
  <div class="card">
    <div class="card-header">
      <h4>Edit Data Siswa</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('data-siswa.update', $siswa->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="container-md">
          <div class="form-group mb-3">
            <label for="nama">Nama Siswa</label>
            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $siswa->nama) }}" required>
            @error('nama')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group mb-3">
            <label for="kelas">Kelas</label>
            <input type="text" class="form-control @error('kelas') is-invalid @enderror" id="kelas" name="kelas" value="{{ old('kelas', $siswa->kelas) }}" required>
            @error('kelas')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group mb-3">
            <label for="nisn">NISN</label>
            <input type="text" class="form-control @error('nisn') is-invalid @enderror" id="nisn" name="nisn" value="{{ old('nisn', $siswa->nisn) }}" required>
            @error('nisn')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group mb-3">
            <label for="angkatan">Angkatan</label>
            <input type="text" class="form-control @error('angkatan') is-invalid @enderror" id="angkatan" name="angkatan" value="{{ old('angkatan', $siswa->angkatan) }}" required>
            @error('angkatan')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- Field jenis_kelamin dengan nilai boolean --}}
          <div class="form-group mb-3">
            <label for="jenis_kelamin">Jenis Kelamin</label>
            <select name="jenis_kelamin" id="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror" required>
                <option value="">Pilih Jenis Kelamin</option>
                {{-- Loop melalui opsi boolean (1 atau 0) --}}
                @foreach($jenisKelaminOptions as $value => $label)
                    <option value="{{ $value }}" {{ (old('jenis_kelamin', $siswa->jenis_kelamin) == $value) ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('jenis_kelamin')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-group mb-3">
            <label for="username">Username</label>
            {{-- Pastikan siswa->user ada sebelum mengakses username --}}
            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $siswa->user->username ?? '') }}" required>
            @error('username')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group mb-3">
            <label for="password">Password Baru</label>
            {{-- Password di edit form biasanya tidak diisi ulang kecuali ingin diganti --}}
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
            @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti password</small>
          </div>
          <div class="form-group mb-3">
            <label for="password_confirmation">Konfirmasi Password Baru</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
          </div>
          <button type="submit" class="btn btn-primary mr-2" title="Update">
            <i class="fas fa-sync-alt"></i>
          </button>
          <a href="{{ route('data-siswa.index') }}" class="btn btn-secondary ml-2" title="Kembali">
            <i class="fas fa-arrow-left"></i> 
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
