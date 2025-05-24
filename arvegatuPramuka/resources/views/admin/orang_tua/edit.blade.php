@extends('layouts.main')

@section('sidebar')
  @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
  <div class="card">
    <div class="card-header">
      <h4>Edit Data Orang Tua</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('data-orang-tua.update', $orangTua->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
          <label>Nama Orang Tua</label>
          <input type="text" name="nama" class="form-control" value="{{ $orangTua->nama }}" required>
        </div>
        <div class="form-group">
          <label>No Handphone</label>
          <input type="text" name="no_hp" class="form-control" value="{{ $orangTua->no_hp }}" required>
        </div>
        <div class="form-group">
            <label>Alamat</label>
            <input type="text" name="alamat" class="form-control" value="{{ $orangTua->alamat }}" required>
          </div>
          <div class="form-group">
            <label>Kelas Pramuka</label>
            <input type="text" name="kelas_pramuka" class="form-control" value="{{ $orangTua->kelas_pramuka }}" required>
          </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('data-orangTua.index') }}" class="btn btn-secondary">Batal</a>
      </form>
    </div>
  </div>
</div>
@endsection
