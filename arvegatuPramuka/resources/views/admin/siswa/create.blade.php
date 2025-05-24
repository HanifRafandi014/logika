<!-- Pastikan jQuery dimuat terlebih dahulu -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
          <div class="form-group">
            <label>Nama Siswa</label>
            <input type="text" name="nama" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Kelas</label>
            <input type="text" name="kelas" class="form-control" required>
          </div>
          <div class="form-group">
            <label>NISN</label>
            <input type="text" name="nisn" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Angkatan</label>
            <input type="text" name="angkatan" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Kelas Pramuka</label>
            <input type="text" name="kelas_pramuka" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="text" name="password" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Konfirmasi Password</label>
            <input type="text" name="password_confirmation" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-success">Simpan</button>
          <a href="{{ route('data-siswa.index') }}" class="btn btn-secondary">Batal</a>
        </form>
      </div>
    </div>
  </div>
@endsection