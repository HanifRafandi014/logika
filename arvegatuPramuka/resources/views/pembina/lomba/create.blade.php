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

                {{-- Dropdown Jenis Lomba --}}
                <div class="mb-3">
                    <label for="dropdown-lomba" class="form-label">Jenis Lomba</label>
                    <select name="variabel_clustering_id" id="dropdown-lomba" class="form-select @error('variabel_clustering_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Jenis Lomba --</option>
                        @foreach ($variabels as $v)
                            <option value="{{ $v->id }}"
                                data-akademik='@json(is_array($v->variabel_akademiks) ? $v->variabel_akademiks : json_decode($v->variabel_akademiks, true) ?? [])'
                                data-nonakademik='@json(is_array($v->variabel_non_akademiks) ? $v->variabel_non_akademiks : json_decode($v->variabel_non_akademiks, true) ?? [])'
                                {{ old('variabel_clustering_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->jenis_lomba }}
                            </option>
                        @endforeach
                    </select>
                    @error('variabel_clustering_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Variabel Akademik --}}
                <div class="mb-3">
                    <label class="form-label">Variabel Akademik</label>
                    <textarea class="form-control" id="variabel-akademik" rows="2" readonly></textarea>
                </div>

                {{-- Variabel Non Akademik --}}
                <div class="mb-3">
                    <label class="form-label">Variabel Non Akademik</label>
                    <textarea class="form-control" id="variabel-non-akademik" rows="2" readonly></textarea>
                </div>

                {{-- Jumlah Siswa --}}
                <div class="mb-3">
                    <label for="jumlah_siswa" class="form-label">Jumlah Siswa</label>
                    <input type="number" class="form-control @error('jumlah_siswa') is-invalid @enderror" name="jumlah_siswa" value="{{ old('jumlah_siswa') }}" required min="1">
                    @error('jumlah_siswa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Status --}}
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

                <button type="submit" class="btn btn-primary" title="Simpan">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="{{ route('lomba.index') }}" class="btn btn-secondary" title="Kembali">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropdown = document.getElementById('dropdown-lomba');

    const updateFields = () => {
        const selected = dropdown.options[dropdown.selectedIndex];
        if (!selected) return;

        const akademik = JSON.parse(selected.dataset.akademik || '[]');
        const nonAkademik = JSON.parse(selected.dataset.nonakademik || '[]');

        document.getElementById('variabel-akademik').value = Array.isArray(akademik) ? akademik.join(', ') : '';
        document.getElementById('variabel-non-akademik').value = Array.isArray(nonAkademik) ? nonAkademik.join(', ') : '';
    };

    dropdown.addEventListener('change', updateFields);

    // Jalankan otomatis jika user kembali dengan pilihan yang sudah ada
    if (dropdown.value) {
        updateFields();
    }
});
</script>
@endpush
