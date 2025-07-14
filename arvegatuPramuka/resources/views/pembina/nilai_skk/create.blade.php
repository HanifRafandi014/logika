@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>

<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Tambah Data Penilaian SKK</h4>
        </div>
        <div class="card-body">
            <form id="skkAssessmentForm" action="{{ route('nilai_skk.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Nama Siswa --}}
                <div class="mb-3">
                    <label for="siswa_display_name" class="form-label">Nama Siswa</label>
                    @if ($selectedSiswaId)
                        <input type="text" class="form-control" value="{{ $selectedSiswaNama }}" disabled>
                        <input type="hidden" name="siswa_id" value="{{ $selectedSiswaId }}">
                    @else
                        <select class="form-control" name="siswa_id" required>
                            <option value="">Pilih Siswa</option>
                            @foreach($siswas as $siswa)
                                <option value="{{ $siswa->id }}" {{ old('siswa_id') == $siswa->id ? 'selected' : '' }}>
                                    {{ $siswa->nama }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    @error('siswa_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Jenis SKK --}}
                <div class="mb-3">
                    <label for="jenis_skk" class="form-label">Jenis SKK</label>
                    <select class="form-control" id="jenis_skk" name="jenis_skk" required>
                        <option value="">Pilih Jenis SKK</option>
                        @foreach($jenisSkks as $jenisSkkOption)
                            <option value="{{ $jenisSkkOption }}" 
                                {{ old('jenis_skk', $selectedJenisSkk ?? '') == $jenisSkkOption ? 'selected' : '' }}>
                                {{ $jenisSkkOption }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_skk')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tingkatan --}}
                <div class="mb-3">
                    <label for="tingkatan" class="form-label">Tingkatan</label>
                    <select class="form-control" id="tingkatan" name="tingkatan" required>
                        <option value="">Pilih Tingkatan</option>
                        @foreach($tingkatans as $tingkatanOption)
                            <option 
                                value="{{ $tingkatanOption }}"
                                {{ old('tingkatan') == $tingkatanOption ? 'selected' : '' }}
                                {{ in_array($tingkatanOption, $disabledTingkatans ?? []) ? 'disabled' : '' }}>
                                {{ ucfirst($tingkatanOption) }}
                            </option>
                        @endforeach
                    </select>
                    @error('tingkatan')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tanggal --}}
                <div class="mb-3">
                    <label for="assessment_date" class="form-label">Tanggal Penilaian</label>
                    <input type="date" class="form-control" name="assessment_date" value="{{ old('assessment_date', date('Y-m-d')) }}" required>
                    @error('assessment_date')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Bukti PDF --}}
                <div class="mb-3">
                    <label for="bukti_pdf" class="form-label">Bukti Penilaian SKK</label>
                    <input type="file" class="form-control" name="bukti_pdf" accept=".pdf">
                    @error('bukti_pdf')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Format yang diizinkan: PDF. Ukuran maksimal: 2MB.</small>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-success">Simpan</button>
                    <a href="{{ route('nilai_skk.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#jenis_skk').on('change', function () {
            const jenis = $(this).val();
            const siswaId = '{{ $selectedSiswaId }}';
            const siswaNama = '{{ $selectedSiswaNama }}';
            const siswaNisn = '{{ $selectedSiswaNisn }}';
            const siswaKelas = '{{ $selectedSiswaKelas }}';

            if (jenis && siswaId) {
                const url = new URL(window.location.href);
                url.searchParams.set('siswa_id', siswaId);
                url.searchParams.set('siswa_nama', siswaNama);
                url.searchParams.set('siswa_nisn', siswaNisn);
                url.searchParams.set('siswa_kelas', siswaKelas);
                url.searchParams.set('jenis_skk', jenis);
                window.location.href = url.toString();
            }
        });
    });
</script>
@endpush
