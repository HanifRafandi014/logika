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
          <label>Nama Alumni</label>
          <input type="text" name="nama" class="form-control" value="{{ $alumni->siswa->nama }}" required>
        </div>
        <div class="form-group">
          <label>Tahun Lulus</label>
          <input type="text" name="tahun_lulus" class="form-control" value="{{ $alumni->tahun_lulus }}" required>
        </div>
        <div class="form-group">
            <label>Pekerjaan</label>
            <input type="text" name="pekerjaan" class="form-control" value="{{ $alumni->pekerjaan }}" required>
          </div>
          <div class="form-group">
            <label>No Handphone</label>
            <input type="text" name="no_hp" class="form-control" value="{{ $alumni->no_hp }}" required>
          </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('data-alumni.index') }}" class="btn btn-secondary">Batal</a>
      </form>
    </div>
  </div>
</div>
@endsection
