@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
    <div class="card">
      <div class="card-header">
        <h4>Edit Data Besaran Biaya</h4>
      </div>
      <div class="card-body">
        <form action="{{ route('data-besaran-biaya.update', $biaya->id) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="form-group">
            <label for="nominal_pagu_kelas">Nominal Pagu Kelas</label>
            <input type="number" name="nominal_pagu_kelas" id="nominal_pagu_kelas" class="form-control @error('nominal_pagu_kelas') is-invalid @enderror" value="{{ old('nominal_pagu_kelas', $biaya->nominal_pagu_kelas) }}" required>
            @error('nominal_pagu_kelas')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
          </div>
          <div class="form-group">
            <label for="nominal_pagu_besar">Nominal Pagu Besar</label>
            <input type="number" name="nominal_pagu_besar" id="nominal_pagu_besar" class="form-control @error('nominal_pagu_besar') is-invalid @enderror" value="{{ old('nominal_pagu_besar', $biaya->nominal_pagu_besar) }}" required>
            @error('nominal_pagu_besar')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
          </div>
          <button type="submit" class="btn btn-primary">Update</button>
          <a href="{{ route('data-besaran-biaya.index') }}" class="btn btn-secondary">Batal</a>
        </form>
      </div>
    </div>
  </div>
@endsection
