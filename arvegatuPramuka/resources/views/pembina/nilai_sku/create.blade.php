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
            <h4 class="card-title">Tambah Data Penilaian SKU</h4>
        </div>
        <div class="card-body">
            <form id="skuAssessmentForm" action="{{ route('nilai_sku.store') }}" method="POST">
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

                    <div class="mb-3">
                        <label for="assessment_date" class="form-label">Tanggal Penilaian</label>
                        <input type="date" class="form-control" id="assessment_date" name="assessment_date" value="{{ old('assessment_date', date('Y-m-d')) }}" required>
                        @error('assessment_date')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <button type="button" id="getSkuButton" class="btn btn-primary">Muat Daftar Penilaian</button>
                    </div>

                    <div class="mb-3">
                        <label for="status_sku_display" class="form-label">Status SKU</label>
                        <input type="text" class="form-control" id="status_sku_display" value="Belum Dimuat" readonly>
                        <input type="hidden" id="overall_status_hidden" value="0">
                    </div>
                </div>

                <div id="sku-detail-section" style="display:none;">
                    <hr>
                    <h5 class="card-title mt-4">Detail Penilaian SKU</h5>
                    <div class="table-responsive">
                        <table id="skuDetailTable" class="display table table-bordered table-hover">
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
                    <a href="{{ route('nilai_sku.index') }}" class="btn btn-secondary mt-3">Batal</a>
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
        const form = $('#skuAssessmentForm');
        const siswaId = $('#siswa_id');
        const tingkatan = $('#tingkatan');
        const btnLoad = $('#getSkuButton');
        const tableBody = $('#skuDetailTable tbody');
        const statusField = $('#status_sku_display');
        const statusHidden = $('#overall_status_hidden');

        let totalItems = 0;
        let checkedSkuIds = new Set(); // Ini adalah Set yang melacak semua ID SKU yang dicentang

        function updateOverallStatus() {
            const currentCheckedCount = checkedSkuIds.size; // Ambil jumlah dari Set
            if (totalItems === 0) {
                statusField.val("Tidak ada SKU");
                statusHidden.val("0");
                form.find('button[type="submit"]').prop('disabled', true);
            } else if (currentCheckedCount === totalItems) {
                statusField.val("Memenuhi");
                statusHidden.val("1");
                form.find('button[type="submit"]').prop('disabled', false);
            } else {
                statusField.val("Tidak Memenuhi");
                statusHidden.val("0");
                form.find('button[type="submit"]').prop('disabled', false);
            }
        }

        // Fungsi ini akan dipanggil setiap kali DataTable digambar ulang (misal: ganti halaman)
        function updateCheckboxesOnDraw() {
            // Iterasi melalui semua checkbox yang saat ini ada di DOM (halaman aktif)
            $('#skuDetailTable tbody .sku-checkbox').each(function() {
                const skuId = $(this).attr('data-sku-id'); // Mengambil ID dari data-attribute
                if (checkedSkuIds.has(skuId)) { // Periksa apakah ID ini ada di Set global
                    $(this).prop('checked', true); // Jika ada, centang checkboxnya
                } else {
                    $(this).prop('checked', false); // Jika tidak, jangan centang
                }
            });
        }

        btnLoad.on('click', function () {
            if (!siswaId.val() || !tingkatan.val()) {
                alert('Pilih siswa dan tingkatan terlebih dahulu.');
                return;
            }

            siswaId.prop('disabled', true);
            tingkatan.prop('disabled', true);
            btnLoad.prop('disabled', true).text('Memuat...');

            // Hancurkan instance DataTable yang mungkin sudah ada sebelum memuat data baru
            if ($.fn.DataTable.isDataTable('#skuDetailTable')) {
                $('#skuDetailTable').DataTable().destroy();
            }
            tableBody.empty(); // Kosongkan isi tabel

            // Clear Set checkedSkuIds saat memuat daftar baru
            checkedSkuIds.clear();

            $.ajax({
                url: "{{ route('nilai_sku.getSkuItemsByTingkatan') }}",
                method: 'GET',
                data: { tingkatan: tingkatan.val() },
                success: function(data) {
                    totalItems = data.length; // Set totalItems dari data yang diambil

                    if (totalItems === 0) {
                        tableBody.append(`<tr><td colspan="3" class="text-center">Tidak ada data SKU</td></tr>`);
                    } else {
                        let num = 1;
                        data.forEach(item => {
                            tableBody.append(`
                                <tr>
                                    <td>${num++}</td>
                                    <td>${item.keterangan_sku}</td>
                                    <td>
                                        {{-- Gunakan data-sku-id untuk menyimpan ID SKU, bukan langsung value --}}
                                        <input type="checkbox" data-sku-id="${item.id}" class="sku-checkbox">
                                    </td>
                                </tr>
                            `);
                        });
                    }

                    // Inisialisasi ulang DataTable
                    const dataTableInstance = $('#skuDetailTable').DataTable({
                        pageLength: 5, // Atur pageLength sesuai keinginan
                        searching: true,
                        // Tambahkan drawCallback untuk memperbarui checkbox setiap kali tabel digambar
                        "drawCallback": function( settings ) {
                            updateCheckboxesOnDraw();
                        }
                    });

                    $('#sku-detail-section').slideDown();

                    // Pasang event listener untuk checkbox. Gunakan event delegation untuk DataTable.
                    // .off('change') digunakan untuk mencegah event listener ganda jika btnLoad diklik lebih dari sekali.
                    $('#skuDetailTable tbody').off('change', '.sku-checkbox').on('change', '.sku-checkbox', function() {
                        const skuId = $(this).attr('data-sku-id'); // Ambil ID dari data-attribute
                        if ($(this).is(':checked')) {
                            checkedSkuIds.add(skuId); // Tambahkan ID ke Set
                        } else {
                            checkedSkuIds.delete(skuId); // Hapus ID dari Set
                        }
                        updateOverallStatus(); // Perbarui status keseluruhan
                    });

                    updateOverallStatus(); // Perbarui status awal setelah data dimuat

                    btnLoad.prop('disabled', false).text('Muat Daftar Penilaian');
                },
                error: function(xhr) {
                    alert("Gagal memuat data: " + xhr.responseText);
                    siswaId.prop('disabled', false);
                    tingkatan.prop('disabled', false);
                    btnLoad.prop('disabled', false).text('Muat Daftar Penilaian');
                }
            });
        });

        // Event listener saat form disubmit
        form.on('submit', function (event) {
            // Hapus semua hidden input 'checked_sku_items[]' yang mungkin sudah ada sebelumnya
            $('input[name="checked_sku_items[]"]').remove();

            // Buat hidden input baru untuk setiap ID yang ada di Set checkedSkuIds
            checkedSkuIds.forEach(skuId => {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'checked_sku_items[]',
                    value: skuId
                }).appendTo(form);
            });

            // Pastikan field siswa_id dan tingkatan tidak disabled agar nilainya terkirim
            siswaId.prop('disabled', false);
            tingkatan.prop('disabled', false);
        });
    });
</script>
@endpush