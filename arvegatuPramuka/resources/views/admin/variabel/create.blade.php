@extends('layouts.main')

@section('sidebar')
  @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
    <div class="card">
        <div class="card-header">
            <h4>Tambah Data Variabel</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('data-variabel.store') }}" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label for="jenis_lomba">Jenis Lomba</label>
                    <input type="text" name="jenis_lomba" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label>Variabel Akademik</label><br>
                    @foreach (['Matematika', 'IPA', 'IPS', 'Olahraga', 'Bahasa Indonesia', 'Bahasa Inggris'] as $item)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="variabel_akademik[]" value="{{ $item }}">
                            <label class="form-check-label">{{ $item }}</label>
                        </div>
                    @endforeach
                </div>

                <div class="form-group mb-3">
                    <label>Variabel Non Akademik</label><br>
                    @foreach (['Nilai Tes Bahasa', 'Nilai TIK', 'Kehadiran', 'Skor Penerapan', 'Nilai Hasta Karya', 'Status SKU', 'Status SKK'] as $item)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="variabel_non_akademik[]" value="{{ $item }}">
                            <label class="form-check-label">{{ $item }}</label>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('data-variabel.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection
