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
        <h4>Tambah Alumni</h4>
      </div>
      <div class="card-body">
        <form action="{{ route('data-alumni.store') }}" method="POST">
          @csrf
          <div class="form-group">
            <label>Nama Alumni</label>
            <input type="text" name="nama" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Tahun Lulus</label>
            <input type="text" name="tahun_lulus" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Pekerjaan</label>
            <input type="text" name="pekerjaan" class="form-control" required>
          </div>
          <div class="form-group">
            <label>No Handphone</label>
            <input type="text" name="no_hp" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-success">Simpan</button>
          <a href="{{ route('data-alumni.index') }}" class="btn btn-secondary">Batal</a>
        </form>
      </div>
    </div>
  </div>
@endsection