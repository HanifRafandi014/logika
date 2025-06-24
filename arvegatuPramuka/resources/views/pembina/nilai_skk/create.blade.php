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
            <form id="skkAssessmentForm" action="{{ route('nilai_skk.store') }}" method="POST">
                @csrf

                <div id="main-info-section">
                    <div class="mb-3">
                        <label for="siswa_id" class="form-label">Nama Siswa</label>
                        <select class="form-control" id="siswa_id" name="siswa_id" required>
                            <option value="">Pilih Siswa</option>
                            @foreach($siswas as $siswa)
                                <option value="{{ $siswa->id }}" {{ old('siswa_id') == $siswa->id ? 'selected' : '' }}>
                                    {{ $siswa->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('siswa_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="tingkatan" class="form-label">Tingkatan</label>
                        <select class="form-control" id="tingkatan" name="tingkatan" required>
                            <option value="">Pilih Tingkatan</option>
                            @foreach($tingkatans as $tingkatanOption)
                                <option value="{{ $tingkatanOption }}" {{ old('tingkatan') == $tingkatanOption ? 'selected' : '' }}>
                                    {{ ucfirst($tingkatanOption) }}
                                </option>
                            @endforeach
                        </select>
                        @error('tingkatan')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- NEW: Dropdown for Jenis SKK --}}
                    <div class="mb-3">
                        <label for="jenis_skk" class="form-label">Jenis SKK</label>
                        <select class="form-control" id="jenis_skk" name="jenis_skk" required>
                            <option value="">Pilih Jenis SKK</option>
                            @foreach($jenisSkks as $jenisSkkOption)
                                <option value="{{ $jenisSkkOption }}" {{ old('jenis_skk') == $jenisSkkOption ? 'selected' : '' }}>
                                    {{ $jenisSkkOption }}
                                </option>
                            @endforeach
                        </select>
                        @error('jenis_skk')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="assessment_date" class="form-label">Tanggal Penilaian</label>
                        <input type="date" class="form-control" id="assessment_date" name="assessment_date" value="{{ old('assessment_date', date('Y-m-d')) }}" required>
                        @error('assessment_date')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <button type="button" id="getSkkButton" class="btn btn-primary">Muat Daftar Penilaian</button>
                    </div>

                    <div class="mb-3">
                        <label for="status_skk_display" class="form-label">Status SKK</label>
                        <input type="text" class="form-control" id="status_skk_display" value="Belum Dimuat" readonly>
                        {{-- The 'overall_status_hidden' is no longer directly used for submission, as overall status is derived.
                             It can be kept for client-side logic or removed if not needed for direct submission.
                             For the purpose of this solution, it's removed from being sent, as the backend calculates status. --}}
                    </div>
                </div>

                <div id="skk-detail-section" style="display:none;">
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
                    <button type="submit" class="btn btn-success mt-3">Simpan</button>
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
        const siswaId = $('#siswa_id');
        const tingkatan = $('#tingkatan');
        const jenisSkk = $('#jenis_skk'); // NEW: Get jenis_skk element
        const btnLoad = $('#getSkkButton');
        const tableBody = $('#skkDetailTable tbody');
        const statusField = $('#status_skk_display');

        let totalItems = 0;
        let checkedSkkIds = new Set(); // Ini adalah Set yang melacak semua ID Skk yang dicentang

        function updateOverallStatus() {
            const currentCheckedCount = checkedSkkIds.size; // Ambil jumlah dari Set
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

        // Fungsi ini akan dipanggil setiap kali DataTable digambar ulang (misal: ganti halaman)
        function updateCheckboxesOnDraw() {
            // Iterasi melalui semua checkbox yang saat ini ada di DOM (halaman aktif)
            $('#skkDetailTable tbody .skk-checkbox').each(function() {
                const skkId = $(this).attr('data-skk-id'); // Mengambil ID dari data-attribute
                if (checkedSkkIds.has(skkId)) { // Periksa apakah ID ini ada di Set global
                    $(this).prop('checked', true); // Jika ada, centang checkboxnya
                } else {
                    $(this).prop('checked', false); // Jika tidak, jangan centang
                }
            });
        }

        btnLoad.on('click', function () {
            if (!siswaId.val() || !tingkatan.val() || !jenisSkk.val()) { // Check for jenis_skk as well
                alert('Pilih siswa, tingkatan, dan jenis SKK terlebih dahulu.');
                return;
            }

            siswaId.prop('disabled', true);
            tingkatan.prop('disabled', true);
            jenisSkk.prop('disabled', true); // Disable jenis_skk dropdown
            btnLoad.prop('disabled', true).text('Memuat...');

            // Hancurkan instance DataTable yang mungkin sudah ada sebelum memuat data baru
            if ($.fn.DataTable.isDataTable('#skkDetailTable')) {
                $('#skkDetailTable').DataTable().destroy();
            }
            tableBody.empty(); // Kosongkan isi tabel

            // Clear Set checkedSkkIds saat memuat daftar baru
            checkedSkkIds.clear();

            $.ajax({
                url: "{{ route('nilai_skk.getSkkItems') }}", // Updated route name
                method: 'GET',
                data: {
                    tingkatan: tingkatan.val(),
                    jenis_skk: jenisSkk.val() // Send jenis_skk
                },
                success: function(data) {
                    totalItems = data.length; // Set totalItems dari data yang diambil

                    if (totalItems === 0) {
                        tableBody.append(`<tr><td colspan="3" class="text-center">Tidak ada data SKK</td></tr>`);
                    } else {
                        let num = 1;
                        data.forEach(item => {
                            tableBody.append(`
                                <tr>
                                    <td>${num++}</td>
                                    <td>${item.keterangan_skk}</td>
                                    <td>
                                        <input type="checkbox" data-skk-id="${item.id}" class="skk-checkbox">
                                    </td>
                                </tr>
                            `);
                        });
                    }

                    // Inisialisasi ulang DataTable
                    const dataTableInstance = $('#skkDetailTable').DataTable({
                        pageLength: 5,
                        searching: true,
                        "drawCallback": function( settings ) {
                            updateCheckboxesOnDraw();
                        }
                    });

                    $('#skk-detail-section').slideDown();

                    // Pasang event listener untuk checkbox. Gunakan event delegation untuk DataTable.
                    $('#skkDetailTable tbody').off('change', '.skk-checkbox').on('change', '.skk-checkbox', function() {
                        const skkId = $(this).attr('data-skk-id'); // Ambil ID dari data-attribute
                        if ($(this).is(':checked')) {
                            checkedSkkIds.add(skkId); // Tambahkan ID ke Set
                        } else {
                            checkedSkkIds.delete(skkId); // Hapus ID dari Set
                        }
                        updateOverallStatus(); // Perbarui status keseluruhan
                    });

                    updateOverallStatus(); // Perbarui status awal setelah data dimuat

                    siswaId.prop('disabled', false);
                    tingkatan.prop('disabled', false);
                    jenisSkk.prop('disabled', false); // Re-enable jenis_skk dropdown
                    btnLoad.prop('disabled', false).text('Muat Daftar Penilaian');
                },
                error: function(xhr) {
                    alert("Gagal memuat data: " + xhr.responseText);
                    siswaId.prop('disabled', false);
                    tingkatan.prop('disabled', false);
                    jenisSkk.prop('disabled', false); // Re-enable jenis_skk dropdown
                    btnLoad.prop('disabled', false).text('Muat Daftar Penilaian');
                }
            });
        });

        // Event listener saat form disubmit
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

            // Pastikan field siswa_id, tingkatan, dan jenis_skk tidak disabled agar nilainya terkirim
            siswaId.prop('disabled', false);
            tingkatan.prop('disabled', false);
            jenisSkk.prop('disabled', false); // Ensure jenis_skk is not disabled
        });
    });
</script>
@endpush