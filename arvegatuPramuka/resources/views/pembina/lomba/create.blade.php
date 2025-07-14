@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Tambah Lomba Baru</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('lomba.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="jenis_lomba" class="form-label">Jenis Lomba</label>
                    <select name="variabel_clustering_id" id="dropdown-lomba" class="form-select @error('variabel_clustering_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Jenis Lomba --</option>
                        @foreach ($variabels as $v)
                            <option value="{{ $v->id }}"
                                data-akademik='@json($v->variabel_akademiks)'
                                data-nonakademik='@json($v->variabel_non_akademiks)'
                                {{ old('variabel_clustering_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->jenis_lomba }}
                            </option>
                        @endforeach
                    </select>
                    @error('variabel_clustering_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Variabel Akademik</label>
                    <textarea class="form-control" id="variabel-akademik" rows="2" readonly></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Variabel Non Akademik</label>
                    <textarea class="form-control" id="variabel-non-akademik" rows="2" readonly></textarea>
                </div>

                <div class="mb-3">
                    <label for="jumlah_siswa" class="form-label">Jumlah Siswa</label>
                    <input type="number" class="form-control @error('jumlah_siswa') is-invalid @enderror" name="jumlah_siswa" value="{{ old('jumlah_siswa') }}" required min="1">
                    @error('jumlah_siswa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status Kompetensi</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('lomba.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('dropdown-lomba').addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];
    const akademik = JSON.parse(selected.dataset.akademik || '[]');
    const nonAkademik = JSON.parse(selected.dataset.nonakademik || '[]');
    document.getElementById('variabel-akademik').value = akademik.join(', ');
    document.getElementById('variabel-non-akademik').value = nonAkademik.join(', ');
});
</script>
@endpush
