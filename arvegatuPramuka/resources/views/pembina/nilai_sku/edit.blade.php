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
            <h4 class="card-title">Edit Data Penilaian SKU</h4>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- IMPORTANT: Add enctype="multipart/form-data" for file uploads --}}
            <form id="skuAssessmentForm" action="{{ route('nilai_sku.update_group', ['siswa_id' => $siswa->id, 'tingkatan' => $tingkatan]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div id="main-info-section">
                    <div class="mb-3">
                        <label for="siswa_id_display" class="form-label">Nama Siswa</label>
                        <input type="text" class="form-control" value="{{ $siswa->nama ?? 'N/A' }}" readonly>
                        <input type="hidden" name="siswa_id" id="siswa_id" value="{{ $siswa->id }}">
                    </div>

                    <div class="mb-3">
                        <label for="tingkatan_display" class="form-label">Tingkatan</label>
                        <input type="text" class="form-control" value="{{ ucfirst($tingkatan) }}" readonly>
                        <input type="hidden" name="tingkatan" id="tingkatan" value="{{ $tingkatan }}">
                    </div>

                    <div class="mb-3">
                        <label for="assessment_date" class="form-label">Tanggal Penilaian</label>
                        {{-- Use $penilaianSku->tanggal for the date --}}
                        <input type="date" class="form-control" id="assessment_date" name="assessment_date" value="{{ old('assessment_date', $penilaianSku->tanggal ?? date('Y-m-d')) }}" required>
                        @error('assessment_date')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- REVERTED: Input for Bukti PDF (global) --}}
                    <div class="mb-3">
                        <label for="bukti_pdf" class="form-label">Bukti Penilaian SKU</label>
                        @if($penilaianSku->bukti_pdf)
                            <p>PDF Saat Ini: <a href="{{ asset($penilaianSku->bukti_pdf) }}" target="_blank">Lihat PDF</a></p>
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
                        <label for="status_sku_display" class="form-label">Status SKU</label>
                        <input type="text" class="form-control" id="status_sku_display" value="Belum Dinilai" readonly>
                    </div>
                </div>

                <div id="sku-detail-section">
                    <hr>
                    <h4 class="card-title" style="padding-top: 30px;">Detail Penilaian SKU</h4>
                    <div class="table-responsive">
                        <table id="skuDetailTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Keterangan</th>
                                    <th>Nilai</th>
                                    {{-- REMOVED: Column for Bukti PDF per item --}}
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Data SKU akan dimuat di sini oleh JavaScript --}}
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-success mt-3">Update Penilaian SKU</button>
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
    $(document).ready(function() {
        const siswaIdField = $('#siswa_id'); // Hidden input
        const tingkatanField = $('#tingkatan'); // Hidden input
        const assessmentDateField = $('#assessment_date');
        const statusSkuDisplay = $('#status_sku_display');
        const skuDetailSection = $('#sku-detail-section');
        const skuDetailTableBody = $('#skuDetailTable tbody');
        const form = $('#skuAssessmentForm');

        let totalSkuItems = 0;
        let checkedSkuIds = new Set(); // Ini adalah Set yang melacak semua ID SKU yang dicentang

        // Data existing assessments dari controller (keyed by manajemen_sku_id)
        const existingAssessmentsKeyed = {!! json_encode($existingAssessmentsKeyed->toArray()) !!};

        function updateOverallStatus() {
            const currentCheckedCount = checkedSkuIds.size;
            if (totalSkuItems === 0) {
                statusSkuDisplay.val('Tidak ada SKU');
                form.find('button[type="submit"]').prop('disabled', true);
            } else if (currentCheckedCount === totalSkuItems) {
                statusSkuDisplay.val('Memenuhi');
                form.find('button[type="submit"]').prop('disabled', false);
            } else {
                statusSkuDisplay.val('Tidak Memenuhi');
                form.find('button[type="submit"]').prop('disabled', false);
            }
        }

        // Fungsi ini akan dipanggil setiap kali DataTable digambar ulang (misal: ganti halaman)
        function updateCheckboxesOnDraw() {
            $('#skuDetailTable tbody .sku-checkbox').each(function() {
                const skuId = $(this).attr('data-sku-id'); // Mengambil ID dari data-attribute
                if (checkedSkuIds.has(skuId)) { // Periksa apakah ID ini ada di Set global
                    $(this).prop('checked', true); // Jika ada, centang checkboxnya
                } else {
                    $(this).prop('checked', false); // Jika tidak, jangan centang
                }
            });
        }

        function loadSkuDetails() {
            const siswaId = siswaIdField.val();
            const tingkatan = tingkatanField.val();

            if (!siswaId || !tingkatan) {
                console.warn('Siswa ID atau Tingkatan kosong, tidak bisa memuat detail SKU.');
                return;
            }

            // Hancurkan instance DataTable yang mungkin sudah ada sebelum memuat data baru
            if ($.fn.DataTable.isDataTable('#skuDetailTable')) {
                $('#skuDetailTable').DataTable().destroy();
            }
            skuDetailTableBody.empty(); // Kosongkan isi tabel

            checkedSkuIds.clear(); // Sangat penting: Bersihkan Set saat memuat ulang

            $.ajax({
                url: "{{ route('nilai_sku.getSkuItemsByTingkatan') }}",
                method: 'GET',
                data: { tingkatan: tingkatan },
                success: function(response) {
                    totalSkuItems = response.length;

                    if (totalSkuItems === 0) {
                        skuDetailTableBody.append('<tr><td colspan="3" class="text-center">Tidak ada SKU untuk tingkatan ini.</td></tr>');
                    } else {
                        let num = 1;
                        response.forEach(function(item) {
                            const skuIdString = String(item.id); // Pastikan item.id adalah string

                            // Periksa apakah item SKU ini sudah dicentang sebelumnya
                            const existingAssessment = existingAssessmentsKeyed[skuIdString];
                            const isCheckedPreviously = existingAssessment && existingAssessment.status === 1;

                            if (isCheckedPreviously) {
                                checkedSkuIds.add(skuIdString); // Tambahkan ID string ke Set global
                            }

                            const row = `
                                <tr>
                                    <td>${num++}</td>
                                    <td>${item.keterangan_sku}</td>
                                    <td>
                                        <input type="checkbox" data-sku-id="${skuIdString}" class="sku-checkbox" ${isCheckedPreviously ? 'checked' : ''}>
                                    </td>
                                </tr>
                            `;
                            skuDetailTableBody.append(row);
                        });
                    }

                    // Inisialisasi DataTable
                    const dataTableInstance = $('#skuDetailTable').DataTable({
                        pageLength: 5,
                        searching: true,
                        "drawCallback": function( settings ) {
                            updateCheckboxesOnDraw(); // Panggil saat draw
                        }
                    });

                    skuDetailSection.slideDown();

                    // Pasang event listener untuk checkbox. Gunakan event delegation.
                    $('#skuDetailTable tbody').off('change', '.sku-checkbox').on('change', '.sku-checkbox', function() {
                        const skuId = $(this).attr('data-sku-id'); // Ambil ID (string) dari data-attribute
                        if ($(this).is(':checked')) {
                            checkedSkuIds.add(skuId);
                        } else {
                            checkedSkuIds.delete(skuId);
                        }
                        updateOverallStatus(); // Perbarui status keseluruhan
                    });

                    updateOverallStatus(); // Perbarui status awal setelah data dimuat dan Set diisi
                },
                error: function(xhr) {
                    alert('Gagal memuat data SKU: ' + xhr.responseText);
                }
            });
        }

        // Panggil loadSkuDetails() saat halaman edit dimuat pertama kali
        loadSkuDetails();

        // Event listener saat form disubmit
        form.on('submit', function(event) {
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
        });
    });
</script>
@endpush
