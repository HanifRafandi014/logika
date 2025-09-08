@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Tambah Data Penilaian SKU</h4>
        </div>
        <div class="card-body">
            <form id="skuAssessmentForm" action="{{ route('nilai_sku.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div id="main-info-section">
                    <div class="mb-3">
                        <label for="siswa_id" class="form-label">Nama Siswa</label>
                        @if ($selectedSiswaId)
                            <input type="text" class="form-control" value="{{ $selectedSiswaNama }}" disabled>
                            <input type="hidden" id="siswa_id_hidden" name="siswa_id" value="{{ $selectedSiswaId }}">
                        @else
                            <select class="form-control" id="siswa_id_dropdown" name="siswa_id" required>
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

                    <div class="mb-3">
                        <label for="tingkatan" class="form-label">Tingkatan</label>
                        <select class="form-control" id="tingkatan" name="tingkatan" required>
                            <option value="">Pilih Tingkatan</option>
                            @foreach($allTingkatans as $tingkatanOption)
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

                    <div class="mb-3">
                        <label for="assessment_date" class="form-label">Tanggal Penilaian</label>
                        <input type="date" class="form-control" id="assessment_date" name="assessment_date" value="{{ old('assessment_date', date('Y-m-d')) }}" required>
                        @error('assessment_date')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="bukti_pdf" class="form-label">Bukti Penilaian SKU</label>
                        <input type="file" class="form-control" id="bukti_pdf" name="bukti_pdf" accept=".pdf">
                        @error('bukti_pdf')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Format yang diizinkan: PDF. Ukuran maksimal: 2MB.</small>
                    </div>

                    <div class="mb-3">
                        <button type="button" id="getSkuButton" class="btn btn-info" title="Muat Penilaian SKU">
                            <i class="fas fa-spinner"></i>
                        </button>
                    </div>

                    <div class="mb-3">
                        <label for="status_sku_display" class="form-label">Status SKU</label>
                        <input type="text" class="form-control" id="status_sku_display" value="Belum Dimuat" readonly>
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
                                    <th>Deskripsi SKU</th>
                                    <th>Checklist Penilaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Data dinamis dimuat oleh JavaScript --}}
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3" title="Simpan Penilaian">
                        <i class="fas fa-save"></i>
                    </button>
                    <a href="{{ route('nilai_sku.index') }}" class="btn btn-secondary mt-3" title="Kembali">
                        <i class="fas fa-arrow-left"></i>
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
        const form = $('#skuAssessmentForm');
        const selectedSiswaId = @json($selectedSiswaId ?? null);
        const siswaIdSource = selectedSiswaId ? $('#siswa_id_hidden') : $('#siswa_id_dropdown');

        const tingkatan = $('#tingkatan');
        const btnLoad = $('#getSkuButton');
        const tableBody = $('#skuDetailTable tbody');
        const statusField = $('#status_sku_display');

        let totalItems = 0;
        let checkedSkuIds = new Set();

        function updateOverallStatus() {
            const currentCheckedCount = checkedSkuIds.size;
            if (totalItems === 0) {
                statusField.val("Tidak ada SKU");
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
            $('#skuDetailTable tbody .sku-checkbox').each(function() {
                const skuId = $(this).attr('data-sku-id');
                if (checkedSkuIds.has(skuId)) {
                    $(this).prop('checked', true);
                } else {
                    $(this).prop('checked', false);
                }
            });
        }

        btnLoad.on('click', function () {
            let currentSiswaId = selectedSiswaId || siswaIdSource.val();

            if (!currentSiswaId || !tingkatan.val()) {
                alert('Pilih siswa dan tingkatan terlebih dahulu.');
                return;
            }

            if (siswaIdSource.attr('id') === 'siswa_id_dropdown') siswaIdSource.prop('disabled', true);
            tingkatan.prop('disabled', true);
            btnLoad.prop('disabled', true).text('Memuat...');

            if ($.fn.DataTable.isDataTable('#skuDetailTable')) {
                $('#skuDetailTable').DataTable().destroy();
            }
            tableBody.empty();
            checkedSkuIds.clear();

            $.ajax({
                url: "{{ route('nilai_sku.getSkuItemsByTingkatan') }}",
                method: 'GET',
                data: { tingkatan: tingkatan.val() },
                success: function(data) {
                    totalItems = data.length;

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
                                        <input type="checkbox" data-sku-id="${item.id}" class="sku-checkbox">
                                    </td>
                                </tr>
                            `);
                        });
                    }

                    const dataTableInstance = $('#skuDetailTable').DataTable({
                        pageLength: 5,
                        searching: true,
                        "drawCallback": function( settings ) {
                            updateCheckboxesOnDraw();
                        }
                    });

                    $('#sku-detail-section').slideDown();

                    $('#skuDetailTable tbody').off('change', '.sku-checkbox').on('change', '.sku-checkbox', function() {
                        const skuId = $(this).attr('data-sku-id');
                        if ($(this).is(':checked')) {
                            checkedSkuIds.add(skuId);
                        } else {
                            checkedSkuIds.delete(skuId);
                        }
                        updateOverallStatus();
                    });

                    updateOverallStatus();

                    if (siswaIdSource.attr('id') === 'siswa_id_dropdown') siswaIdSource.prop('disabled', false);
                    tingkatan.prop('disabled', false);
                    btnLoad.prop('disabled', false).text('Muat Daftar Penilaian');
                },
                error: function(xhr) {
                    alert("Gagal memuat data: " + xhr.responseText);
                    if (siswaIdSource.attr('id') === 'siswa_id_dropdown') siswaIdSource.prop('disabled', false);
                    tingkatan.prop('disabled', false);
                    btnLoad.prop('disabled', false).text('Muat Daftar Penilaian');
                }
            });
        });

        form.on('submit', function (event) {
            $('input[name="checked_sku_items[]"]').remove();

            checkedSkuIds.forEach(skuId => {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'checked_sku_items[]',
                    value: skuId
                }).appendTo(form);
            });

            if (siswaIdSource.attr('id') === 'siswa_id_dropdown') siswaIdSource.prop('disabled', false);
            tingkatan.prop('disabled', false);
        });
    });
</script>
@endpush