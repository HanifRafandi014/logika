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
          <div class="form-group">
            <label for="nama">Nama Siswa</label>
            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $siswa->nama) }}" required>
            @error('nama')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="kelas">Kelas</label>
            <input type="text" class="form-control @error('kelas') is-invalid @enderror" id="kelas" name="kelas" value="{{ old('kelas', $siswa->kelas) }}" required>
            @error('kelas')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="nisn">NISN</label>
            <input type="text" class="form-control @error('nisn') is-invalid @enderror" id="nisn" name="nisn" value="{{ old('nisn', $siswa->nisn) }}" required>
            @error('nisn')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="angkatan">Angkatan</label>
            <input type="text" class="form-control @error('angkatan') is-invalid @enderror" id="angkatan" name="angkatan" value="{{ old('angkatan', $siswa->angkatan) }}" required>
            @error('angkatan')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="kelas_pramuka">Kelas Pramuka</label>
            <input type="text" class="form-control @error('kelas_pramuka') is-invalid @enderror" id="kelas_pramuka" name="kelas_pramuka" value="{{ old('kelas_pramuka', $siswa->kelas_pramuka) }}" required>
            @error('kelas_pramuka')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $siswa->user->username) }}" required>
            @error('username')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="password">Password Baru</label>
            <input type="text" class="form-control @error('password') is-invalid @enderror" id="password" name="password" value="{{ old('password', $siswa->password) }}">
            @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti password</small>
          </div>
          <div class="form-group">
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
