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
            <h4 class="card-title">Edit Data Penilaian SKK</h4>
        </div>
        <div class="card-body">
            {{-- IMPORTANT: Add enctype="multipart/form-data" for file uploads --}}
            <form id="skkAssessmentForm" action="{{ route('nilai_skk.update_group', ['siswa_id' => $siswa->id, 'tingkatan' => $tingkatan, 'jenis_skk' => $jenis_skk]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div id="main-info-section">
                    {{-- Nama Siswa (Hidden Input) --}}
                    <div class="mb-3">
                        <label for="siswa_nama" class="form-label">Nama Siswa</label>
                        <input type="text" class="form-control" value="{{ $siswa->nama ?? 'N/A' }}" readonly>
                        <input type="hidden" id="siswa_id" name="siswa_id" value="{{ $siswa->id }}">
                    </div>

                    {{-- Tingkatan (Hidden Input) --}}
                    <div class="mb-3">
                        <label for="tingkatan_display" class="form-label">Tingkatan</label>
                        <input type="text" class="form-control" value="{{ ucfirst($tingkatan) }}" readonly>
                        <input type="hidden" id="tingkatan" name="tingkatan" value="{{ $tingkatan }}">
                    </div>

                    {{-- Jenis SKK (Hidden Input) --}}
                    <div class="mb-3">
                        <label for="jenis_skk_display" class="form-label">Jenis SKK</label>
                        <input type="text" class="form-control" value="{{ $jenis_skk }}" readonly>
                        <input type="hidden" id="jenis_skk" name="jenis_skk" value="{{ $jenis_skk }}">
                    </div>

                    <div class="mb-3">
                        <label for="assessment_date" class="form-label">Tanggal Penilaian</label>
                        {{-- Use $penilaianSkk->tanggal for the date --}}
                        <input type="date" class="form-control" id="assessment_date" name="assessment_date" value="{{ old('assessment_date', $penilaianSkk->tanggal ?? date('Y-m-d')) }}" required>
                        @error('assessment_date')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- REVERTED: Input for Bukti PDF (global) --}}
                    <div class="mb-3">
                        <label for="bukti_pdf" class="form-label">Bukti Penilaian SKK</label>
                        @if($penilaianSkk->bukti_pdf)
                            <p>PDF Saat Ini: <a href="{{ asset($penilaianSkk->bukti_pdf) }}" target="_blank">Lihat PDF</a></p>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remove_bukti_pdf" name="remove_bukti_pdf" value="1">
                                <label class="form-check-label" for="remove_bukti_pdf">Hapus PDF saat ini</label>
                            </div>
                        @endif
                        <input type="file" class="form-control" id="bukti_pdf" name="bukti_pdf" accept=".pdf">
                        @error('bukti_pdf')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Format yang diizinkan: PDF. Ukuran maksimal: 2MB.</small>
                    </div>

                    <div class="mb-3">
                        <label for="status_skk_display" class="form-label">Status SKK</label>
                        <input type="text" class="form-control" id="status_skk_display" value="Belum Dimuat" readonly>
                    </div>
                </div>

                <div id="skk-detail-section">
                    <hr>
                    <h5 class="card-title mt-4">Detail Penilaian SKK</h5>
                    <div class="table-responsive">
                        <table id="skkDetailTable" class="display table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Keterangan</th>
                                    <th>Checklist Penilaian</th>
                                    {{-- REMOVED: Column for Bukti PDF per item --}}
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Data dinamis dimuat oleh JavaScript --}}
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3" title="Update Penilaian SKK">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <a href="{{ route('nilai_skk.index') }}" class="btn btn-secondary mt-3" title="Kembali">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(function () {
        const form = $('#skkAssessmentForm');
        const siswaId = $('#siswa_id');
        const tingkatan = $('#tingkatan');
        const jenisSkk = $('#jenis_skk');
        const tableBody = $('#skkDetailTable tbody');
        const statusField = $('#status_skk_display');

        let totalItems = 0;
        let checkedSkkIds = new Set();

        // Data existing assessments dari controller (keyed by manajemen_skk_id)
        const existingAssessmentsKeyed = {!! json_encode($existingAssessmentsKeyed->toArray()) !!};

        function updateOverallStatus() {
            const currentCheckedCount = checkedSkkIds.size;
            if (totalItems === 0) {
                statusField.val("Tidak ada SKK");
                form.find('button[type="submit"]').prop('disabled', true);
            } else if (currentCheckedCount === totalItems) {
                statusField.val("Memenuhi");
                form.find('button[type="submit"]').prop('disabled', false);
            } else {
                statusField.val("Tidak Memenuhi");
                form.find('button[type="submit"]').prop('disabled', false);
            }
        }

        function updateCheckboxesOnDraw() {
            $('#skkDetailTable tbody .skk-checkbox').each(function() {
                const skkId = $(this).attr('data-skk-id');
                if (checkedSkkIds.has(skkId)) {
                    $(this).prop('checked', true);
                } else {
                    $(this).prop('checked', false);
                }
            });
        }

        // Function to load SKK items based on selected tingkatan and jenis_skk
        function loadSkkItems(selectedTingkatan, selectedJenisSkk) {
            if ($.fn.DataTable.isDataTable('#skkDetailTable')) {
                $('#skkDetailTable').DataTable().destroy();
            }
            tableBody.empty();
            checkedSkkIds.clear(); // Clear the set for a fresh load

            $.ajax({
                url: "{{ route('nilai_skk.getSkkItems') }}",
                method: 'GET',
                data: {
                    tingkatan: selectedTingkatan,
                    jenis_skk: selectedJenisSkk
                },
                success: function(data) {
                    totalItems = data.length;

                    if (totalItems === 0) {
                        tableBody.append(`<tr><td colspan="3" class="text-center">Tidak ada data SKK</td></tr>`);
                        form.find('button[type="submit"]').prop('disabled', true);
                    } else {
                        let num = 1;
                        data.forEach(item => {
                            const skkId = String(item.id);
                            const existingAssessment = existingAssessmentsKeyed[skkId];
                            const isCheckedInitially = existingAssessment && existingAssessment.status === 1;

                            if (isCheckedInitially) {
                                checkedSkkIds.add(skkId);
                            }

                            tableBody.append(`
                                <tr>
                                    <td>${num++}</td>
                                    <td>${item.keterangan_skk}</td>
                                    <td>
                                        <input type="checkbox" data-skk-id="${skkId}" class="skk-checkbox" ${isCheckedInitially ? 'checked' : ''}>
                                    </td>
                                </tr>
                            `);
                        });
                        form.find('button[type="submit"]').prop('disabled', false);
                    }

                    const dataTableInstance = $('#skkDetailTable').DataTable({
                        pageLength: 5,
                        searching: true,
                        "drawCallback": function( settings ) {
                            updateCheckboxesOnDraw();
                        }
                    });

                    $('#skkDetailTable tbody').off('change', '.skk-checkbox').on('change', '.skk-checkbox', function() {
                        const skkId = $(this).attr('data-skk-id');
                        if ($(this).is(':checked')) {
                            checkedSkkIds.add(skkId);
                        } else {
                            checkedSkkIds.delete(skkId);
                        }
                        updateOverallStatus();
                    });

                    updateOverallStatus();
                },
                error: function(xhr) {
                    alert("Gagal memuat data: " + xhr.responseText);
                    form.find('button[type="submit"]').prop('disabled', true);
                }
            });
        }

        // Initial load for edit page - ensures data is loaded automatically
        const initialSiswaId = siswaId.val();
        const initialTingkatan = tingkatan.val();
        const initialJenisSkk = jenisSkk.val();

        if (initialSiswaId && initialTingkatan && initialJenisSkk) {
            loadSkkItems(initialTingkatan, initialJenisSkk);
        }

        form.on('submit', function (event) {
            // Hapus semua hidden input 'checked_skk_items[]' yang mungkin sudah ada sebelumnya
            $('input[name="checked_skk_items[]"]').remove();

            // Buat hidden input baru untuk setiap ID yang ada di Set checkedSkkIds
            checkedSkkIds.forEach(skkId => {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'checked_skk_items[]',
                    value: skkId
                }).appendTo(form);
            });

            // Ensure hidden inputs are not disabled so their values are submitted
            siswaId.prop('disabled', false);
            tingkatan.prop('disabled', false);
            jenisSkk.prop('disabled', false);
        });
    });
</script>
@endpush
