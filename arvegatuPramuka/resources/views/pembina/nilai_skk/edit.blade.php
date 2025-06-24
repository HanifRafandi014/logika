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
            <form id="skkAssessmentForm" action="{{ route('nilai_skk.update_group', ['siswa_id' => $siswa->id, 'tingkatan' => $tingkatan, 'jenis_skk' => $jenis_skk]) }}" method="POST">
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
                        <input type="date" class="form-control" id="assessment_date" name="assessment_date" value="{{ old('assessment_date', $penilaianSkk->tanggal ?? date('Y-m-d')) }}" required>
                        @error('assessment_date')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tombol "Muat Daftar Penilaian" disembunyikan karena daftar akan dimuat otomatis --}}
                    {{-- <div class="mb-3">
                        <button type="button" id="getSkkButton" class="btn btn-primary">Muat Daftar Penilaian</button>
                    </div> --}}

                    <div class="mb-3">
                        <label for="status_skk_display" class="form-label">Status SKK</label>
                        <input type="text" class="form-control" id="status_skk_display" value="Belum Dimuat" readonly>
                    </div>
                </div>

                <div id="skk-detail-section"> {{-- Remove style="display:none;" here as it should be visible initially --}}
                    <hr>
                    <h5 class="card-title mt-4">Detail Penilaian SKK</h5>
                    <div class="table-responsive">
                        <table id="skkDetailTable" class="display table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Keterangan</th>
                                    <th>Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Data dinamis dimuat oleh JavaScript --}}
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-success mt-3">Update</button>
                    <a href="{{ route('nilai_skk.index') }}" class="btn btn-secondary mt-3">Batal</a>
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
        // Siswa ID, Tingkatan, dan Jenis SKK sekarang diambil dari hidden input
        const siswaId = $('#siswa_id');
        const tingkatan = $('#tingkatan');
        const jenisSkk = $('#jenis_skk');
        // const btnLoad = $('#getSkkButton'); // Tombol ini sudah disembunyikan
        const tableBody = $('#skkDetailTable tbody');
        const statusField = $('#status_skk_display');

        let totalItems = 0;
        let checkedSkkIds = new Set();

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
        function loadSkkItems(selectedTingkatan, selectedJenisSkk, initialLoad = false) {
            // Disabled property removed for siswaId, tingkatan, jenisSkk since they are now hidden inputs
            // btnLoad.prop('disabled', true).text('Memuat...'); // Tombol disembunyikan

            if ($.fn.DataTable.isDataTable('#skkDetailTable')) {
                $('#skkDetailTable').DataTable().destroy();
            }
            tableBody.empty();
            checkedSkkIds.clear();

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
                        form.find('button[type="submit"]').prop('disabled', true); // Disable submit if no items
                    } else {
                        let num = 1;
                        data.forEach(item => {
                            // Check if this item was already assessed and its status was true
                            // The `existingAssessments` data is passed from the controller to the view
                            const isCheckedInitially = initialLoad && (typeof {{ Js::from($existingAssessments->pluck('status', 'manajemen_skk_id')) }}[item.id] !== 'undefined' && {{ Js::from($existingAssessments->pluck('status', 'manajemen_skk_id')) }}[item.id] === 1);

                            if (isCheckedInitially) {
                                checkedSkkIds.add(String(item.id)); // Add to set if checked
                            }

                            tableBody.append(`
                                <tr>
                                    <td>${num++}</td>
                                    <td>${item.keterangan_skk}</td>
                                    <td>
                                        <input type="checkbox" data-skk-id="${item.id}" class="skk-checkbox" ${isCheckedInitially ? 'checked' : ''}>
                                    </td>
                                </tr>
                            `);
                        });
                        form.find('button[type="submit"]').prop('disabled', false); // Enable submit if there are items
                    }

                    const dataTableInstance = $('#skkDetailTable').DataTable({
                        pageLength: 5,
                        searching: true,
                        "drawCallback": function( settings ) {
                            updateCheckboxesOnDraw();
                        }
                    });

                    // section detail sudah tidak hidden lagi, jadi ini tidak perlu:
                    // $('#skk-detail-section').slideDown();

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

                    // Remove re-enabling disabled properties as they are now hidden inputs
                    // siswaId.prop('disabled', false);
                    // tingkatan.prop('disabled', false);
                    // jenisSkk.prop('disabled', false);
                    // btnLoad.prop('disabled', false).text('Muat Daftar Penilaian'); // Tombol disembunyikan
                },
                error: function(xhr) {
                    alert("Gagal memuat data: " + xhr.responseText);
                    // Remove re-enabling disabled properties
                    // siswaId.prop('disabled', false);
                    // tingkatan.prop('disabled', false);
                    // jenisSkk.prop('disabled', false);
                    // btnLoad.prop('disabled', false).text('Muat Daftar Penilaian'); // Tombol disembunyikan
                    form.find('button[type="submit"]').prop('disabled', true); // Disable submit on error
                }
            });
        }

        // Initial load for edit page - ensures data is loaded automatically
        const initialSiswaId = siswaId.val();
        const initialTingkatan = tingkatan.val();
        const initialJenisSkk = jenisSkk.val();

        if (initialSiswaId && initialTingkatan && initialJenisSkk) {
            loadSkkItems(initialTingkatan, initialJenisSkk, true);
        }

        // Remove the click event for btnLoad as it's no longer visible
        // btnLoad.on('click', function () {
        //     if (!siswaId.val() || !tingkatan.val() || !jenisSkk.val()) {
        //         alert('Pilih siswa, tingkatan, dan jenis SKK terlebih dahulu.');
        //         return;
        //     }
        //     loadSkkItems(tingkatan.val(), jenisSkk.val());
        // });

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