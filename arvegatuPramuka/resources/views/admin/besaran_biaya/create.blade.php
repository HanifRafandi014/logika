<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.admin')
@endsection

@section('content')
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4>Tambah Data Besaran Biaya</h4>
            </div>

            <div class="card-body">
                <form action="{{ route('data-besaran-biaya.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="nominal_pagu_kelas">Nominal Pagu Kelas</label>
                        <input type="number" name="nominal_pagu_kelas" id="nominal_pagu_kelas" class="form-control @error('nominal_pagu_kelas') is-invalid @enderror" value="{{ old('nominal_pagu_kelas') }}" required>
                        @error('nominal_pagu_kelas')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="nominal_pagu_besar">Nominal Pagu Besar</label>
                        <input type="number" name="nominal_pagu_besar" id="nominal_pagu_besar" class="form-control @error('nominal_pagu_besar') is-invalid @enderror" value="{{ old('nominal_pagu_besar') }}" required>
                        @error('nominal_pagu_besar')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary" title="Simpan Data">
                        <i class="fas fa-save"></i>
                    </button>
                    <a href="{{ route('data-besaran-biaya.index') }}" class="btn btn-secondary" title="Kembali">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                </form>
            </div>
        </div>
    </div>
@endsection
