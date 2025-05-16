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
        <h4>Tambah Orang Tua</h4>
      </div>
      <div class="card-body">
        <form action="{{ route('data-orang-tua.store') }}" method="POST">
          @csrf
          <div class="form-group">
            <label>Nama Orang Tua</label>
            <input type="text" name="nama" class="form-control" required>
          </div>
          <div class="form-group">
            <label>No Handphone</label>
            <input type="text" name="kelas" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Alamat</label>
            <input type="text" name="nisn" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Nama Siswa</label>
            <input type="text" name="angkatan" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-success">Simpan</button>
          <a href="{{ route('data-orang-tua.index') }}" class="btn btn-secondary">Batal</a>
        </form>
      </div>
    </div>
  </div>
@endsection