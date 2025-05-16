@extends('layouts.main')

@section('sidebar')
  @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
  <div class="card">
    <div class="card-header">
      <h4>Edit Data Pembina</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('data-pembina.update', $pembina->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
          <label>Nama Pembina</label>
          <input type="text" name="nama" class="form-control" value="{{ $pembina->nama }}" required>
        </div>
        <div class="form-group">
          <label>NIP</label>
          <input type="text" name="nip" class="form-control" value="{{ $pembina->nip }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('data-pembina.index') }}" class="btn btn-secondary">Batal</a>
      </form>
    </div>
  </div>
</div>
@endsection
