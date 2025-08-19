@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Edit Lomba</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('lomba.update', $lomba->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="jenis_lomba" class="form-label">Jenis Lomba</label>
                    <select name="variabel_clustering_id" id="dropdown-lomba" class="form-select" disabled>
                        @foreach ($variabels as $v)
                            <option value="{{ $v->id }}" 
                                data-akademik='@json($v->variabel_akademiks)'
                                data-nonakademik='@json($v->variabel_non_akademiks)'
                                {{ $lomba->variabel_clustering_id == $v->id ? 'selected' : '' }}>
                                {{ $v->jenis_lomba }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Variabel Akademik</label>
                    <textarea class="form-control" id="variabel-akademik" rows="2" readonly>{{ implode(', ', $lomba->variabel->variabel_akademiks ?? []) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Variabel Non Akademik</label>
                    <textarea class="form-control" id="variabel-non-akademik" rows="2" readonly>{{ implode(', ', $lomba->variabel->variabel_non_akademiks ?? []) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="jumlah_siswa" class="form-label">Jumlah Siswa</label>
                    <input type="number" class="form-control @error('jumlah_siswa') is-invalid @enderror" name="jumlah_siswa" value="{{ old('jumlah_siswa', $lomba->jumlah_siswa) }}" required min="1">
                    @error('jumlah_siswa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status Kompetensi</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="1" {{ old('status', $lomba->status) == 1 ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('status', $lomba->status) == 0 ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" title="Update">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <a href="{{ route('lomba.index') }}" class="btn btn-secondary" title="Kembali">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
