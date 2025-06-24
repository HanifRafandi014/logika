@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
    <div class="card">
      <div class="card-header">
        <h4>Tambah Siswa</h4>
      </div>
      <div class="card-body">
        <form action="{{ route('data-siswa.store') }}" method="POST">
          @csrf
          <div class="form-group mb-3">
            <label for="nama">Nama Siswa</label>
            <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
            @error('nama')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group mb-3">
            <label for="kelas">Kelas</label>
            <input type="text" name="kelas" id="kelas" class="form-control @error('kelas') is-invalid @enderror" value="{{ old('kelas') }}" required>
            @error('kelas')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group mb-3">
            <label for="nisn">NISN</label>
            <input type="text" name="nisn" id="nisn" class="form-control @error('nisn') is-invalid @enderror" value="{{ old('nisn') }}" required>
            @error('nisn')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group mb-3">
            <label for="angkatan">Angkatan</label>
            <input type="text" name="angkatan" id="angkatan" class="form-control @error('angkatan') is-invalid @enderror" value="{{ old('angkatan') }}" required>
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
                    <option value="{{ $value }}" {{ old('jenis_kelamin') == $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('jenis_kelamin')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-group mb-3">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
            @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group mb-3">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group mb-3">
            <label for="password_confirmation">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-success">Simpan</button>
          <a href="{{ route('data-siswa.index') }}" class="btn btn-secondary">Batal</a>
        </form>
      </div>
    </div>
  </div>
@endsection
