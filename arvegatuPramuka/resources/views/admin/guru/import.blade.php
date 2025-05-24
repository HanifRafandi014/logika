@extends('layouts.main')

@section('sidebar')
  @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-6 offset-md-3">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Import Excel</h5>
      <a href="{{ route('data-guru.index') }}" class="btn-close" aria-label="Close"></a>
    </div>
    <div class="card-body">
      @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif

      <form action="{{ route('admin.guru.import-guru') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
          <label for="file" class="form-label">Pilih File Excel</label>
          <input type="file" class="form-control" name="file" id="file" required accept=".xlsx, .xls">
        </div>
        <button type="submit" class="btn btn-success">
          <i class="fas fa-file-excel"></i> Import Excel
        </button>
      </form>
    </div>
  </div>
</div>
@endsection
