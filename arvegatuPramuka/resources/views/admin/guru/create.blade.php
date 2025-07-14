@extends('layouts.main')

@section('sidebar')
  @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
  <div class="card">
    <div class="card-header">
      <h4>Tambah Pembina</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('data-guru.store') }}" method="POST">
        @csrf
        <div class="form-group">
          <label>Nama Guru</label>
          <input type="text" name="nama" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Kelas</label>
            <input type="text" name="kelas" class="form-control" required>
          </div>
        <div class="form-group">
            <label>Mata Pelajaran</label>
            <input type="text" name="mata_pelajaran" class="form-control" required>
          </div>
        <div class="form-group">
          <label>NIP</label>
          <input type="text" name="nip" class="form-control" required>
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
        <div class="form-group">
            <label>Pembina Pramuka</label>
            <select name="pembina_pramuka" class="form-control" required>
              <option value="1">Ya</option>
              <option value="0">Tidak</option>
            </select>
          </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('data-guru.index') }}" class="btn btn-secondary">Batal</a>
      </form>
    </div>
  </div>
</div>
@endsection
