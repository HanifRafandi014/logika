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

            <form id="skuAssessmentForm" action="{{ route('nilai_sku.update_group', ['siswa_id' => $siswa->id, 'tingkatan' => $tingkatan]) }}" method="POST">
                @csrf
                @method('PUT')

                <div id="main-info-section">
                    <div class="mb-3">
                        <label for="siswa_id_display" class="form-label">Nama Siswa</label>
                        <select class="form-control" id="siswa_id_display" disabled>
                            <option value="">Pilih Siswa</option>
                            @foreach($siswas as $s)
                                <option value="{{ $s->id }}" {{ $siswa->id == $s->id ? 'selected' : '' }}>
                                    {{ $s->nama }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="siswa_id" id="siswa_id" value="{{ $siswa->id }}">
                    </div>

                    <div class="mb-3">
                        <label for="tingkatan_display" class="form-label">Tingkatan</label>
                        <select class="form-control" id="tingkatan_display" disabled>
                            <option value="">Pilih Tingkatan</option>
                            @foreach($tingkatans as $tingkatanOption)
                                <option value="{{ $tingkatanOption }}" {{ $tingkatan == $tingkatanOption ? 'selected' : '' }}>
                                    {{ ucfirst($tingkatanOption) }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="tingkatan" id="tingkatan" value="{{ $tingkatan }}">
                    </div>

                    <div class="mb-3">
                        <label for="assessment_date" class="form-label">Tanggal Penilaian</label>
                        <input type="date" class="form-control" id="assessment_date" name="assessment_date" value="{{ old('assessment_date', $penilaianSku->tanggal) }}" required>
                        @error('assessment_date')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                         <button type="button" id="getSkuButton" class="btn btn-primary" style="display:none;">Muat Detail Penilaian SKU</button>
                    </div>

                    <div class="mb-3">
                        <label for="status_sku_display" class="form-label">Status SKU</label>
                        <input type="text" class="form-control" id="status_sku_display" value="Belum Dinilai" readonly>
                        <input type="hidden" id="overall_status_hidden" value="0">
                    </div>
                </div>

                <div id="sku-detail-section" style="display:none;">
                    <hr>
                    <h4 class="card-title" style="padding-top: 30px;">Detail Penilaian SKU</h4>
                    <div class="table-responsive">
                        <table id="skuDetailTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Keterangan</th>
                                    <th>Nilai</th>
                                    <th>Tanggal</th>
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
        const overallStatusHidden = $('#overall_status_hidden');
        const skuDetailSection = $('#sku-detail-section');
        const skuDetailTableBody = $('#skuDetailTable tbody');
        const form = $('#skuAssessmentForm'); // Define form here for broader scope

        let totalSkuItems = 0;
        let checkedSkuIds = new Set(); // Ini adalah Set yang melacak semua ID SKU yang dicentang

        // Data penilaian yang sudah ada, dikirim dari controller.
        // KONVERSI existingAssessments menjadi format yang konsisten (key string, value boolean)
        const rawExistingAssessments = @json($existingAssessments ?? []);
        const existingAssessmentsProcessed = {};
        for (const skuId in rawExistingAssessments) {
            if (rawExistingAssessments.hasOwnProperty(skuId)) {
                existingAssessmentsProcessed[String(skuId)] = rawExistingAssessments[skuId].status === 1;
            }
        }
        console.log('--- DEBUG START (Edit Page) ---');
        console.log('existingAssessmentsProcessed (dari backend, setelah diolah):', existingAssessmentsProcessed);

        const initialAssessmentDate = '{{ $penilaianSku->tanggal ?? date('Y-m-d') }}';
        assessmentDateField.val(initialAssessmentDate);

        function updateOverallStatus() {
            const currentCheckedCount = checkedSkuIds.size;
            console.log('Current checkedSkuIds Set size:', currentCheckedCount, 'Total SKU items:', totalItems);

            if (totalItems === 0) {
                statusSkuDisplay.val('Tidak ada SKU');
                overallStatusHidden.val('0');
                form.find('button[type="submit"]').prop('disabled', true);
            } else if (currentCheckedCount === totalItems) {
                statusSkuDisplay.val('Memenuhi');
                overallStatusHidden.val('1');
                form.find('button[type="submit"]').prop('disabled', false);
            } else {
                statusSkuDisplay.val('Tidak Memenuhi');
                overallStatusHidden.val('0');
                form.find('button[type="submit"]').prop('disabled', false);
            }
        }

        // Fungsi ini akan dipanggil setiap kali DataTable digambar ulang (misal: ganti halaman)
        function updateCheckboxesOnDraw() {
            // Iterasi melalui semua checkbox yang saat ini ada di DOM (halaman aktif)
            $('#skuDetailTable tbody .sku-checkbox').each(function() {
                const skuId = $(this).val(); // Mengambil ID dari value attribute (string)
                if (checkedSkuIds.has(skuId)) { // Periksa apakah ID ini ada di Set global
                    $(this).prop('checked', true); // Jika ada, centang checkboxnya
                } else {
                    $(this).prop('checked', false); // Jika tidak, jangan centang
                }
            });
            console.log('Checkboxes updated on DataTables draw.');
        }

        function loadSkuDetails() {
            const siswaId = siswaIdField.val();
            const tingkatan = tingkatanField.val();
            const assessmentDate = assessmentDateField.val();

            if (!siswaId || !tingkatan) {
                console.warn('Siswa ID atau Tingkatan kosong, tidak bisa memuat detail SKU.');
                return;
            }

            // Hancurkan instance DataTable yang mungkin sudah ada sebelum memuat data baru
            if ($.fn.DataTable.isDataTable('#skuDetailTable')) {
                $('#skuDetailTable').DataTable().destroy();
                console.log('Existing DataTable destroyed for new load.');
            }
            skuDetailTableBody.empty(); // Kosongkan isi tabel

            checkedSkuIds.clear(); // Sangat penting: Bersihkan Set saat memuat ulang
            console.log('checkedSkuIds cleared before AJAX call.');

            $.ajax({
                url: "{{ route('nilai_sku.getSkuItemsByTingkatan') }}",
                method: 'GET',
                data: { tingkatan: tingkatan },
                success: function(response) {
                    totalSkuItems = response.length;
                    console.log('API response (SKU items untuk tingkatan ini):', response);

                    if (totalSkuItems === 0) {
                        skuDetailTableBody.append('<tr><td colspan="4" class="text-center">Tidak ada SKU untuk tingkatan ini.</td></tr>');
                        console.warn('No SKU items found for tingkatan:', tingkatan);
                    } else {
                        let num = 1;
                        response.forEach(function(item) {
                            const skuIdString = String(item.id); // Pastikan item.id adalah string

                            // Periksa apakah item SKU ini sudah dicentang sebelumnya
                            const isCheckedPreviously = existingAssessmentsProcessed[skuIdString] === true;

                            if (isCheckedPreviously) {
                                checkedSkuIds.add(skuIdString); // Tambahkan ID string ke Set global
                                console.log(`Added SKU ${skuIdString} to checkedSkuIds (was checked previously).`); // Debug log
                            } else {
                                console.log(`SKU ${skuIdString} was NOT checked previously.`); // Debug log
                            }

                            const row = `
                                <tr>
                                    <td>${num++}</td>
                                    <td>${item.keterangan_sku}</td>
                                    <td>
                                        <input type="checkbox" value="${skuIdString}" class="sku-checkbox" ${isCheckedPreviously ? 'checked' : ''}>
                                    </td>
                                    <td>${assessmentDate}</td>
                                </tr>
                            `;
                            skuDetailTableBody.append(row);
                        });
                    }

                    // Inisialisasi DataTable
                    const dataTableInstance = $('#skuDetailTable').DataTable({
                        pageLength: 5, // Atur pageLength sesuai keinginan
                        searching: true,
                        "drawCallback": function( settings ) {
                            updateCheckboxesOnDraw(); // Panggil saat draw
                        }
                    });
                    console.log('DataTable initialized.');

                    skuDetailSection.slideDown();

                    // Pasang event listener untuk checkbox. Gunakan event delegation.
                    $('#skuDetailTable tbody').off('change', '.sku-checkbox').on('change', '.sku-checkbox', function() {
                        const skuId = $(this).val(); // Ambil ID (string) dari value attribute
                        if ($(this).is(':checked')) {
                            checkedSkuIds.add(skuId);
                        } else {
                            checkedSkuIds.delete(skuId);
                        }
                        updateOverallStatus(); // Perbarui status keseluruhan
                        console.log('Checkbox changed. checkedSkuIds now:', Array.from(checkedSkuIds));
                    });

                    updateOverallStatus(); // Perbarui status awal setelah data dimuat dan Set diisi
                    console.log('checkedSkuIds after load and initial population:', Array.from(checkedSkuIds));
                    console.log('Initial checkboxes applied. Check UI.'); // Debug log

                },
                error: function(xhr) {
                    alert('Gagal memuat data SKU: ' + xhr.responseText);
                    console.error('AJAX error loading SKU items:', xhr.responseText);
                }
            });
            console.log('loadSkuDetails AJAX call initiated.');
        }

        // Panggil loadSkuDetails() saat halaman edit dimuat pertama kali
        loadSkuDetails();

        // Event listener saat form disubmit
        form.on('submit', function(event) {
            console.log('Form submitted! Preparing payload...');
            // Hapus semua hidden input 'checked_sku_items[]' yang mungkin sudah ada sebelumnya
            $('input[name="checked_sku_items[]"]').remove();
            console.log('Old hidden inputs removed.');

            // Buat hidden input baru untuk setiap ID yang ada di Set checkedSkuIds
            checkedSkuIds.forEach(skuId => {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'checked_sku_items[]',
                    value: skuId
                }).appendTo(form);
            });
            console.log('New hidden inputs added based on checkedSkuIds:', Array.from(checkedSkuIds));
            console.log('--- DEBUG END (Edit Page) ---');
        });
    });
</script>
@endpush