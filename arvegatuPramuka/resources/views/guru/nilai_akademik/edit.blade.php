@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.guru')
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Edit Nilai Akademik</h4>
        </div>
        <div class="card-body">
            {{-- Form for updating the record. Note the PUT method and passing $nilaiAkademik->id --}}
            <form action="{{ route('nilai_akademik.update', $nilaiAkademik->id) }}" method="POST">
                @csrf
                @method('PUT') {{-- Laravel's way to simulate a PUT request for updates --}}

                <div class="mb-3">
                    <label for="siswa_id" class="form-label">Siswa</label>
                    <select class="form-control" id="siswa_id" name="siswa_id" required>
                        <option value="">Pilih Siswa</option>
                        @foreach ($siswas as $siswa)
                            {{-- Check if the current siswa is the one associated with this nilaiAkademik --}}
                            <option value="{{ $siswa->id }}" {{ (old('siswa_id', $nilaiAkademik->siswa_id) == $siswa->id) ? 'selected' : '' }}>
                                {{ $siswa->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('siswa_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="mata_pelajaran" class="form-label">Mata Pelajaran</label>
                    {{-- Populate with existing data, use old() for sticky form --}}
                    <input type="text" class="form-control" id="mata_pelajaran" name="mata_pelajaran" value="{{ old('mata_pelajaran', $nilaiAkademik->mata_pelajaran) }}" required>
                    @error('mata_pelajaran')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="semester" class="form-label">Semester</label>
                    <select class="form-control" id="semester" name="semester" required>
                        <option value="">Pilih Semester</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester }}" {{ old('semester', $nilaiAkademik->semester) == $semester ? 'selected' : '' }}>{{ $semester }}</option>
                        @endforeach
                    </select>
                    @error('semester')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="nilai" class="form-label">Nilai</label>
                    {{-- Populate with existing data, use old() for sticky form --}}
                    <input type="number" class="form-control" id="nilai" name="nilai" value="{{ old('nilai', $nilaiAkademik->nilai) }}" min="0" max="100" required>
                    @error('nilai')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Perbarui Nilai</button>
                <a href="{{ route('nilai_akademik.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection