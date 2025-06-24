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
                @csrf {{-- Token CSRF for form security --}}

                <div class="mb-3">
                    <label for="jenis_lomba" class="form-label">Jenis Lomba</label>
                    <input type="text" class="form-control @error('jenis_lomba') is-invalid @enderror" id="jenis_lomba" name="jenis_lomba" value="{{ old('jenis_lomba') }}" required>
                    @error('jenis_lomba')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="jumlah_siswa" class="form-label">Jumlah Siswa</label>
                    <input type="number" class="form-control @error('jumlah_siswa') is-invalid @enderror" id="jumlah_siswa" name="jumlah_siswa" value="{{ old('jumlah_siswa') }}" required min="1">
                    @error('jumlah_siswa')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Mata Pelajaran</label>
                    @foreach($nilaiAkademiks as $nilaiAkademik)
                        <div class="form-check">
                            <input class="form-check-input @error('nilai_akademiks') is-invalid @enderror" type="checkbox" name="nilai_akademiks[]" id="nilai_akademik_{{ $nilaiAkademik->id }}" value="{{ $nilaiAkademik->id }}"
                                {{ in_array($nilaiAkademik->id, old('nilai_akademiks', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="nilai_akademik_{{ $nilaiAkademik->id }}">
                                {{ $nilaiAkademik->mata_pelajaran }}
                            </label>
                        </div>
                    @endforeach
                    {{-- Display error for the group of checkboxes --}}
                    @error('nilai_akademiks')
                        <div class="invalid-feedback d-block"> {{-- Use d-block to make the error visible for checkboxes --}}
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Kategori Terkait (Nilai Non-Akademik)</label>
                    @foreach($nilaiNonAkademiks as $nilaiNonAkademik)
                        <div class="form-check">
                            <input class="form-check-input @error('nilai_non_akademiks') is-invalid @enderror" type="checkbox" name="nilai_non_akademiks[]" id="nilai_non_akademik_{{ $nilaiNonAkademik->id }}" value="{{ $nilaiNonAkademik->id }}"
                                {{ in_array($nilaiNonAkademik->id, old('nilai_non_akademiks', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="nilai_non_akademik_{{ $nilaiNonAkademik->id }}">
                                {{ $nilaiNonAkademik->kategori }}
                            </label>
                        </div>
                    @endforeach
                    {{-- Display error for the group of checkboxes --}}
                    @error('nilai_non_akademiks')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input @error('status') is-invalid @enderror" id="status" name="status" value="1" {{ old('status') ? 'checked' : '' }}>
                    <label class="form-check-label" for="status">Status Aktif</label>
                    @error('status')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Simpan Lomba</button>
                <a href="{{ route('lomba.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection