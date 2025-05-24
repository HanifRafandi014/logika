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
      <form action="{{ route('data-pembina.store') }}" method="POST">
        @csrf
        <div class="form-group">
          <label>Nama Pembina</label>
          <input type="text" name="nama" class="form-control" required>
        </div>
        <div class="form-group">
          <label>NIP</label>
          <input type="text" name="nip" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('data-pembina.index') }}" class="btn btn-secondary">Batal</a>
      </form>
    </div>
  </div>
</div>
@endsection
