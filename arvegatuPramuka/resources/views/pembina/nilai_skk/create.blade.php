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
                    <select class="form-control" id="tingkatan" name="tingkatan" required disabled>
                        <option value="">Pilih Tingkatan</option>
                    </select>
                    @error('tingkatan')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tanggal Penilaian --}}
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

                {{-- Tombol Muat dan Status --}}
                <div class="mb-3">
                    <button type="button" id="getSkkButton" class="btn btn-info" title="Muat Penilaian SKK">
                        <i class="fas fa-spinner"></i>
                    </button>
                </div>

                <div class="mb-3">
                    <label for="status_skk_display" class="form-label">Status SKK</label>
                    <input type="text" class="form-control" id="status_skk_display" value="Belum Dimuat" readonly>
                </div>

                {{-- Detail Penilaian SKK --}}
                <div id="skk-detail-section" style="display:none;">
                    <hr>
                    <h5 class="card-title mt-4">Detail Penilaian SKK</h5>
                    <div class="table-responsive">
                        <table id="skkDetailTable" class="display table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Deskripsi SKK</th>
                                    <th>Checklist Penilaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Data dinamis dari JavaScript --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary mt-3" title="Simpan Penilaian">
                        <i class="fas fa-save"></i>
                    </button>
                    <a href="{{ route('nilai_skk.index') }}" class="btn btn-secondary mt-3" title="Kembali">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(function () {
    const form = $('#skkAssessmentForm');
    const jenisSkk = $('#jenis_skk');
    const tingkatan = $('#tingkatan');
    const btnLoad = $('#getSkkButton');
    const tableBody = $('#skkDetailTable tbody');
    const statusField = $('#status_skk_display');

    let totalItems = 0;
    let checkedSkkIds = new Set();

    // --- DISABLE tingkatan di awal ---
    tingkatan.prop('disabled', true);

    // Saat jenis_skk berubah, cek tingkatan otomatis
    jenisSkk.on('change', function () {
        const siswaId = $('input[name="siswa_id"]').val();
        const selectedJenis = $(this).val();

        if (!siswaId || !selectedJenis) {
            tingkatan.html('<option value="">Pilih Tingkatan</option>').prop('disabled', true);
            return;
        }

        // Panggil API untuk dapatkan tingkatan berikutnya
        $.ajax({
            url: "{{ route('nilai_skk.next_tingkatan') }}",
            method: 'GET',
            data: {
                siswa_id: siswaId,
                jenis_skk: selectedJenis
            },
            success: function (res) {
                if (res.allowed) {
                    tingkatan.html(`<option value="${res.allowed}" selected>${res.allowed}</option>`);
                } else {
                    tingkatan.html('<option value="">Semua tingkatan sudah dinilai</option>');
                }
                tingkatan.prop('disabled', true); // tetap disabled
            },
            error: function (xhr) {
                console.error(xhr);
                tingkatan.html('<option value="">Gagal memuat tingkatan</option>').prop('disabled', true);
            }
        });
    });

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
            $(this).prop('checked', checkedSkkIds.has(skkId));
        });
    }

    btnLoad.on('click', function () {
        if (!jenisSkk.val() || !tingkatan.val()) {
            alert('Pilih jenis SKK dan pastikan tingkatan terisi otomatis.');
            return;
        }

        // Disable input sementara saat loading
        jenisSkk.prop('disabled', true);
        tingkatan.prop('disabled', true);
        btnLoad.prop('disabled', true).text('Memuat...');

        // Reset tabel dan data checklist
        if ($.fn.DataTable.isDataTable('#skkDetailTable')) {
            $('#skkDetailTable').DataTable().destroy();
        }
        tableBody.empty();
        checkedSkkIds.clear();

        $.ajax({
            url: "{{ route('nilai_skk.getSkkItems') }}",
            method: 'GET',
            data: { jenis_skk: jenisSkk.val(), tingkatan: tingkatan.val() },
            success: function(data) {
                totalItems = data.length;

                if (totalItems === 0) {
                    tableBody.append(`<tr><td colspan="3" class="text-center">Tidak ada data SKK</td></tr>`);
                } else {
                    let num = 1;
                    data.forEach(item => {
                        tableBody.append(`
                            <tr>
                                <td>${num++}</td>
                                <td>${item.keterangan_skk}</td>
                                <td class="text-center">
                                    <input type="checkbox" data-skk-id="${item.id}" class="skk-checkbox">
                                </td>
                            </tr>
                        `);
                    });
                }

                // Inisialisasi DataTable
                $('#skkDetailTable').DataTable({
                    pageLength: 5,
                    searching: true,
                    ordering: false,
                    drawCallback: updateCheckboxesOnDraw
                });

                $('#skk-detail-section').slideDown();

                // Event checklist
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

                // Enable kembali jenis SKK, tingkatan tetap disabled
                jenisSkk.prop('disabled', false);
                tingkatan.prop('disabled', true);
                btnLoad.prop('disabled', false).text('Muat Daftar Penilaian');
            },
            error: function(xhr) {
                alert("Gagal memuat data: " + xhr.responseText);
                jenisSkk.prop('disabled', false);
                tingkatan.prop('disabled', true);
                btnLoad.prop('disabled', false).text('Muat Daftar Penilaian');
            }
        });
    });

    // Saat submit, kirim ID checklist sebagai input hidden
    form.on('submit', function () {
        $('input[name="checked_skk_items[]"]').remove();
        checkedSkkIds.forEach(skkId => {
            $('<input>').attr({
                type: 'hidden',
                name: 'checked_skk_items[]',
                value: skkId
            }).appendTo(form);
        });

        // Pastikan select tidak disabled saat submit
        jenisSkk.prop('disabled', false);
        tingkatan.prop('disabled', false);
    });
});
</script>


